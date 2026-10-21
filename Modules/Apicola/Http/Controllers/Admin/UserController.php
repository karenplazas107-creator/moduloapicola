<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\Role;
use Modules\SICA\Entities\App;
use Modules\SICA\Entities\EPS;
use Modules\SICA\Entities\PopulationGroup;
use Modules\SICA\Entities\PensionEntity;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Program;
use Modules\SICA\Entities\Apprentice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Muestra la lista de usuarios del módulo Apícola.
     */
    public function index(Request $request)
    {
        // Asegurar que existan los roles básicos de Apícola
        $this->ensureApicolaRolesExist();

        // Query base para usuarios vinculados al módulo Apícola
        $query = User::withTrashed()
            ->with(['person.apprentices.course.program', 'roles'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('slug', ['apicola.admin', 'apicola.aprendiz']);
            });

        // Filtro por texto (Nombre, Apellido, Documento, Nickname, Correo)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nickname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('person', function ($qp) use ($search) {
                      $qp->where('first_name', 'like', "%{$search}%")
                         ->orWhere('first_last_name', 'like', "%{$search}%")
                         ->orWhere('second_last_name', 'like', "%{$search}%")
                         ->orWhere('document_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por rol
        if ($request->filled('role') && $request->role !== 'all') {
            $roleSlug = $request->role;
            $query->whereHas('roles', function ($q) use ($roleSlug) {
                $q->where('slug', $roleSlug);
            });
        }

        // Filtro por estado
        if ($request->filled('estado') && $request->estado !== 'all') {
            if ($request->estado === 'activo') {
                $query->whereNull('deleted_at');
            } elseif ($request->estado === 'inactivo') {
                $query->whereNotNull('deleted_at');
            }
        }

        $users = $query->orderBy('id', 'desc')->get();

        // Cálculo de métricas
        $baseCountQuery = User::withTrashed()->whereHas('roles', function ($q) {
            $q->whereIn('slug', ['apicola.admin', 'apicola.aprendiz']);
        });

        $totalUsuarios = (clone $baseCountQuery)->count();
        $totalAdmins = (clone $baseCountQuery)->whereHas('roles', function ($q) {
            $q->where('slug', 'apicola.admin');
        })->count();
        $totalAprendices = (clone $baseCountQuery)->whereHas('roles', function ($q) {
            $q->where('slug', 'apicola.aprendiz');
        })->count();
        $totalActivos = (clone $baseCountQuery)->whereNull('deleted_at')->count();

        $metrics = [
            'total' => $totalUsuarios,
            'admins' => $totalAdmins,
            'aprendices' => $totalAprendices,
            'activos' => $totalActivos,
        ];

        // Roles disponibles para asignación
        $rolesDisponibles = Role::whereIn('slug', ['apicola.admin', 'apicola.aprendiz'])->get();

        // Cursos activos para autocompletado de Ficha y Programa
        $activeCourses = Course::with('program')
            ->where('status', 'Activo')
            ->orderBy('code', 'desc')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'users' => $users,
                'metrics' => $metrics
            ]);
        }

        return view('apicola::Admin.Usuarios.index', compact('users', 'metrics', 'rolesDisponibles', 'activeCourses'));
    }

    /**
     * Consulta si una persona ya existe en el sistema SICA por su número de documento.
     */
    public function searchPerson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_number' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingrese un número de documento válido.'
            ], 422);
        }

        $documentNumber = $request->input('document_number');
        $person = Person::withTrashed()->where('document_number', $documentNumber)->first();

        if ($person) {
            // Verificar si la persona ya tiene un usuario registrado
            $existingUser = User::withTrashed()->where('person_id', $person->id)->with('roles')->first();
            $apprentice = Apprentice::where('person_id', $person->id)->with('course.program')->latest()->first();

            $hasApicola = false;
            if ($existingUser) {
                $hasApicola = $existingUser->roles->contains(function ($r) {
                    return in_array($r->slug, ['apicola.admin', 'apicola.aprendiz']);
                });
            }

            return response()->json([
                'success' => true,
                'found' => true,
                'person' => [
                    'id' => $person->id,
                    'document_type' => $person->document_type ?? 'Cédula de ciudadanía',
                    'document_number' => $person->document_number,
                    'first_name' => $person->first_name,
                    'first_last_name' => $person->first_last_name,
                    'second_last_name' => $person->second_last_name,
                    'last_name' => trim(($person->first_last_name ?? '') . ' ' . ($person->second_last_name ?? '')),
                    'personal_email' => $person->personal_email ?: ($existingUser?->email ?? ''),
                    'ficha' => $apprentice?->course?->code ?? '',
                    'programa' => $apprentice?->course?->program?->name ?? '',
                ],
                'has_user' => (bool) $existingUser,
                'has_apicola' => $hasApicola,
                'user' => $existingUser ? [
                    'id' => $existingUser->id,
                    'nickname' => $existingUser->nickname,
                    'email' => $existingUser->email,
                    'is_active' => !$existingUser->trashed(),
                    'roles' => $existingUser->roles->pluck('name', 'slug'),
                    'has_apicola' => $hasApicola,
                ] : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => false,
            'message' => 'La persona no se encuentra registrada en SICA.'
        ]);
    }

    /**
     * Almacena un nuevo usuario y su persona/aprendiz asociada en el módulo Apícola.
     */
    public function store(Request $request)
    {
        $documentNumber = preg_replace('/[^0-9]/', '', $request->document_number ?? '');

        // 1. Identificar si la persona y/o usuario ya existen en SICA
        $person = null;
        $existingUser = null;

        if (!empty($documentNumber)) {
            $person = Person::withTrashed()->where('document_number', $documentNumber)->first();
            if ($person) {
                $existingUser = User::withTrashed()->where('person_id', $person->id)->first();
            }
        }

        $rules = [
            'first_name' => 'required|string|max:60',
            'last_name' => 'required|string|max:120',
            'document_number' => 'required|numeric',
            'ficha' => 'nullable|string|max:50',
            'programa' => 'nullable|string|max:200',
            'role_slug' => 'nullable|in:apicola.admin,apicola.aprendiz',
        ];

        $messages = [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.numeric' => 'El número de documento debe ser numérico.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Por favor ingrese un correo electrónico válido.',
            'email.unique' => 'El correo electrónico ya está registrado con otro usuario en el sistema.',
            'password.required' => 'La contraseña es obligatoria (mínimo 6 caracteres).',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'nickname.unique' => 'El nombre de usuario (nickname) ya está en uso.',
        ];

        if ($existingUser) {
            $rules['email'] = ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($existingUser->id)];
            $rules['password'] = 'nullable|string|min:6';
            $rules['nickname'] = ['nullable', 'string', 'max:50', Rule::unique('users', 'nickname')->ignore($existingUser->id)];
        } else {
            $rules['email'] = 'required|email|max:100|unique:users,email';
            $rules['password'] = 'required|string|min:6';
            $rules['nickname'] = 'nullable|string|max:50|unique:users,nickname';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Separar apellidos en primer apellido y segundo apellido
            $lastNameRaw = trim($request->last_name ?? $request->apellidos ?? '');
            $parts = preg_split('/\s+/', $lastNameRaw);
            $firstLastName = mb_strtoupper($parts[0] ?? '');
            $secondLastName = count($parts) > 1 ? mb_strtoupper(implode(' ', array_slice($parts, 1))) : null;
            $documentType = $request->document_type ?: 'Cédula de ciudadanía';

            // 1. Guardar o actualizar datos de la persona
            if ($person) {
                if (method_exists($person, 'trashed') && $person->trashed()) {
                    $person->restore();
                }

                $person->update([
                    'document_type' => $documentType,
                    'first_name' => mb_strtoupper(trim($request->first_name)),
                    'first_last_name' => $firstLastName,
                    'second_last_name' => $secondLastName,
                    'personal_email' => trim($request->email),
                ]);
            } else {
                $epsId = EPS::where('name', 'like', '%no registra%')->value('id') ?? (EPS::first()?->id ?? 0);
                $popGroupId = PopulationGroup::where('name', 'like', '%ningun%')->value('id') ?? (PopulationGroup::first()?->id ?? 22);
                $pensionId = PensionEntity::where('name', 'like', '%no registra%')->value('id') ?? (PensionEntity::first()?->id ?? 1);

                $person = Person::create([
                    'document_type' => $documentType,
                    'document_number' => $documentNumber,
                    'first_name' => mb_strtoupper(trim($request->first_name)),
                    'first_last_name' => $firstLastName,
                    'second_last_name' => $secondLastName,
                    'personal_email' => trim($request->email),
                    'eps_id' => $epsId,
                    'population_group_id' => $popGroupId,
                    'pension_entity_id' => $pensionId,
                ]);
            }

            // 2. Crear o actualizar la cuenta de usuario
            if ($existingUser) {
                $user = $existingUser;
                if ($user->trashed()) {
                    $user->restore();
                }

                $user->email = trim($request->email);
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                if ($request->filled('nickname')) {
                    $user->nickname = trim($request->nickname);
                }
                $user->save();
            } else {
                $nickname = trim($request->nickname ?? '');
                if (empty($nickname)) {
                    $emailPrefix = explode('@', $request->email)[0];
                    $baseNick = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $emailPrefix));
                    if (empty($baseNick)) {
                        $baseNick = strtolower(substr($request->first_name, 0, 1) . $firstLastName);
                        $baseNick = preg_replace('/[^a-zA-Z0-9]/', '', $baseNick);
                    }
                    if (empty($baseNick)) {
                        $baseNick = 'user' . rand(100, 999);
                    }
                    $nickname = $baseNick;
                    $counter = 1;
                    while (User::where('nickname', $nickname)->exists()) {
                        $nickname = $baseNick . $counter;
                        $counter++;
                    }
                }

                $user = User::create([
                    'nickname' => $nickname,
                    'email' => trim($request->email),
                    'person_id' => $person->id,
                    'password' => Hash::make($request->password),
                    'email_verified_at' => now(),
                ]);
            }

            // 3. Asignar rol seleccionado para Apícola
            $roleSlug = $request->role_slug ?: 'apicola.aprendiz';
            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }

            // 4. Asignar Ficha y Programa si se especificaron
            $cleanCode = preg_replace('/[^0-9]/', '', $request->ficha ?? '');
            if (!empty($cleanCode)) {
                $course = Course::where('code', (int) $cleanCode)->first();

                if (!$course) {
                    $programName = $request->filled('programa') ? mb_strtoupper(trim($request->programa)) : 'PROGRAMA FORMACIÓN SENA';
                    $program = Program::where('name', $programName)->first();

                    if (!$program) {
                        $maxSofia = (int) (Program::max('sofia_code') ?: 100000);
                        $program = Program::create([
                            'name' => $programName,
                            'sofia_code' => $maxSofia + 1,
                            'program_type' => 'Técnico',
                            'training_type' => 'Titulada',
                            'modality' => 'Presencial',
                            'maximum_duration' => 12,
                            'fic' => 'No',
                            'months_lectiva' => 6,
                            'months_productiva' => 6,
                        ]);
                    }

                    $muniId = DB::table('municipalities')->where('id', 4796)->exists() ? 4796 : (DB::table('municipalities')->value('id') ?? 1);

                    $course = Course::create([
                        'code' => (int) $cleanCode,
                        'program_id' => $program->id,
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->addYear()->toDateString(),
                        'status' => 'Activo',
                        'deschooling' => 'Presencial',
                        'municipality_id' => $muniId,
                        'person_id' => $person->id,
                    ]);
                } elseif ($request->filled('programa')) {
                    $programName = mb_strtoupper(trim($request->programa));
                    if (!$course->program || $course->program->name !== $programName) {
                        $program = Program::where('name', $programName)->first();
                        if (!$program) {
                            $maxSofia = (int) (Program::max('sofia_code') ?: 100000);
                            $program = Program::create([
                                'name' => $programName,
                                'sofia_code' => $maxSofia + 1,
                                'program_type' => 'Técnico',
                                'training_type' => 'Titulada',
                                'modality' => 'Presencial',
                                'maximum_duration' => 12,
                                'fic' => 'No',
                                'months_lectiva' => 6,
                                'months_productiva' => 6,
                            ]);
                        }
                        $course->update(['program_id' => $program->id]);
                    }
                }

                Apprentice::updateOrCreate(
                    ['person_id' => $person->id],
                    [
                        'course_id' => $course->id,
                        'apprentice_status' => 'EN FORMACIÓN'
                    ]
                );
            }

            DB::commit();

            $successMessage = $existingUser 
                ? "Usuario '{$user->nickname}' vinculado exitosamente al módulo Apícola con el rol asignado." 
                : "Usuario registrado exitosamente con acceso a Apícola.";

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'user' => $user->load(['person.apprentices.course.program', 'roles'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra la información de un usuario para edición.
     */
    public function show($id)
    {
        $user = User::withTrashed()->with(['person', 'roles'])->findOrFail($id);

        $apicolaRole = $user->roles->first(function ($r) {
            return in_array($r->slug, ['apicola.admin', 'apicola.aprendiz']);
        });

        // Buscar aprendiz vinculado a la persona
        $apprentice = null;
        if ($user->person) {
            $apprentice = Apprentice::where('person_id', $user->person->id)
                ->with('course.program')
                ->latest()
                ->first();
        }

        $lastName = '';
        if ($user->person) {
            $lastName = trim(($user->person->first_last_name ?? '') . ' ' . ($user->person->second_last_name ?? ''));
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'is_active' => !$user->trashed(),
                'role_slug' => $apicolaRole ? $apicolaRole->slug : 'apicola.aprendiz',
                'first_name' => $user->person?->first_name ?? '',
                'last_name' => $lastName,
                'document_number' => $user->person?->document_number ?? '',
                'ficha' => $apprentice?->course?->code ?? '',
                'programa' => $apprentice?->course?->program?->name ?? '',
                'person' => [
                    'id' => $user->person?->id,
                    'document_type' => $user->person?->document_type ?? 'Cédula de ciudadanía',
                    'document_number' => $user->person?->document_number ?? '',
                    'first_name' => $user->person?->first_name ?? '',
                    'first_last_name' => $user->person?->first_last_name ?? '',
                    'second_last_name' => $user->person?->second_last_name ?? '',
                    'last_name' => $lastName,
                    'ficha' => $apprentice?->course?->code ?? '',
                    'programa' => $apprentice?->course?->program?->name ?? '',
                ]
            ]
        ]);
    }

    /**
     * Actualiza los datos y rol de un usuario existente.
     */
    public function update(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $rules = [
            'first_name' => 'required|string|max:60',
            'last_name' => 'required|string|max:120',
            'document_number' => 'nullable|numeric',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_slug' => 'nullable|in:apicola.admin,apicola.aprendiz',
            'ficha' => 'nullable|string|max:50',
            'programa' => 'nullable|string|max:200',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $lastNameRaw = trim($request->last_name ?? $request->apellidos ?? '');
            $parts = preg_split('/\s+/', $lastNameRaw);
            $firstLastName = mb_strtoupper($parts[0] ?? '');
            $secondLastName = count($parts) > 1 ? mb_strtoupper(implode(' ', array_slice($parts, 1))) : null;

            // 1. Actualizar persona
            if ($user->person) {
                $personData = [
                    'first_name' => mb_strtoupper(trim($request->first_name)),
                    'first_last_name' => $firstLastName,
                    'second_last_name' => $secondLastName,
                    'personal_email' => trim($request->email),
                ];
                if ($request->filled('document_number')) {
                    $personData['document_number'] = $request->document_number;
                }
                $user->person->update($personData);
            }

            // 2. Actualizar usuario
            $user->email = trim($request->email);

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // 3. Sincronizar rol de Apícola
            $roleSlug = $request->role_slug ?: 'apicola.aprendiz';
            $allApicolaRoles = Role::whereIn('slug', ['apicola.admin', 'apicola.aprendiz'])->pluck('id')->toArray();
            $newRole = Role::where('slug', $roleSlug)->first();

            if ($newRole) {
                $user->roles()->detach($allApicolaRoles);
                $user->roles()->attach($newRole->id);
            }

            // 4. Actualizar o crear Ficha y Programa
            $cleanCode = preg_replace('/[^0-9]/', '', $request->ficha ?? '');
            if (!empty($cleanCode) && $user->person) {
                $course = Course::where('code', (int) $cleanCode)->first();

                if (!$course) {
                    $programName = $request->filled('programa') ? mb_strtoupper(trim($request->programa)) : 'PROGRAMA FORMACIÓN SENA';
                    $program = Program::where('name', $programName)->first();

                    if (!$program) {
                        $maxSofia = (int) (Program::max('sofia_code') ?: 100000);
                        $program = Program::create([
                            'name' => $programName,
                            'sofia_code' => $maxSofia + 1,
                            'program_type' => 'Técnico',
                            'training_type' => 'Titulada',
                            'modality' => 'Presencial',
                            'maximum_duration' => 12,
                            'fic' => 'No',
                            'months_lectiva' => 6,
                            'months_productiva' => 6,
                        ]);
                    }

                    $muniId = DB::table('municipalities')->where('id', 4796)->exists() ? 4796 : (DB::table('municipalities')->value('id') ?? 1);

                    $course = Course::create([
                        'code' => (int) $cleanCode,
                        'program_id' => $program->id,
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->addYear()->toDateString(),
                        'status' => 'Activo',
                        'deschooling' => 'Presencial',
                        'municipality_id' => $muniId,
                        'person_id' => $user->person->id,
                    ]);
                } elseif ($request->filled('programa')) {
                    $programName = mb_strtoupper(trim($request->programa));
                    if (!$course->program || $course->program->name !== $programName) {
                        $program = Program::where('name', $programName)->first();
                        if (!$program) {
                            $maxSofia = (int) (Program::max('sofia_code') ?: 100000);
                            $program = Program::create([
                                'name' => $programName,
                                'sofia_code' => $maxSofia + 1,
                                'program_type' => 'Técnico',
                                'training_type' => 'Titulada',
                                'modality' => 'Presencial',
                                'maximum_duration' => 12,
                                'fic' => 'No',
                                'months_lectiva' => 6,
                                'months_productiva' => 6,
                            ]);
                        }
                        $course->update(['program_id' => $program->id]);
                    }
                }

                Apprentice::updateOrCreate(
                    ['person_id' => $user->person->id],
                    [
                        'course_id' => $course->id,
                        'apprentice_status' => 'EN FORMACIÓN'
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado con éxito.',
                'user' => $user->load(['person.apprentices.course.program', 'roles'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alterna el estado activo / inactivo de un usuario (SoftDeletes).
     */
    public function toggleStatus($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if (auth()->check() && auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes desactivar tu propia cuenta en sesión.'
            ], 403);
        }

        if ($user->trashed()) {
            $user->restore();
            $nuevoEstado = 'Activo';
            $message = "El usuario '{$user->nickname}' ha sido activado correctamente.";
        } else {
            $user->delete();
            $nuevoEstado = 'Inactivo';
            $message = "El usuario '{$user->nickname}' ha sido desactivado.";
        }

        return response()->json([
            'success' => true,
            'nuevo_estado' => $nuevoEstado,
            'message' => $message
        ]);
    }

    /**
     * Garantiza que los roles de Apícola existan en la base de datos vinculados a la App Apícola.
     */
    private function ensureApicolaRolesExist()
    {
        $app = App::where('name', 'like', '%apicol%')
                  ->orWhere('url', 'like', '%apicol%')
                  ->first();

        $appId = $app ? $app->id : 1;

        Role::firstOrCreate(
            ['slug' => 'apicola.admin'],
            [
                'name' => 'Administrador Apícola',
                'description' => 'Administrador exclusivo del módulo Apícola.',
                'description_english' => 'Exclusive Administrator for the Beekeeping module.',
                'app_id' => $appId,
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'apicola.aprendiz'],
            [
                'name' => 'Aprendiz Apícola',
                'description' => 'Rol de Aprendiz para el módulo Apícola.',
                'description_english' => 'Apprentice Role for the Beekeeping module.',
                'app_id' => $appId,
            ]
        );
    }
}

