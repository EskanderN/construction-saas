<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectParticipant;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем компанию
        $company = Company::create([
            'name' => 'СтройИнвест',
        ]);

        // Создаем пользователей
        $director = User::create([
            'company_id' => $company->id,
            'name' => 'Иванов Иван (Директор)',
            'email' => 'director@example.com',
            'password' => Hash::make('password'),
            'role' => 'director',
        ]);

        $deputy = User::create([
            'company_id' => $company->id,
            'name' => 'Петров Петр (Зам. директора)',
            'email' => 'deputy@example.com',
            'password' => Hash::make('password'),
            'role' => 'deputy_director',
        ]);

        $pto = User::create([
            'company_id' => $company->id,
            'name' => 'Сидоров Сидор (ПТО)',
            'email' => 'pto@example.com',
            'password' => Hash::make('password'),
            'role' => 'pto',
        ]);

        $supply = User::create([
            'company_id' => $company->id,
            'name' => 'Смирнов Алексей (Снабжение)',
            'email' => 'supply@example.com',
            'password' => Hash::make('password'),
            'role' => 'supply',
        ]);

        $projectManager = User::create([
            'company_id' => $company->id,
            'name' => 'Козлов Николай (РП)',
            'email' => 'pm@example.com',
            'password' => Hash::make('password'),
            'role' => 'project_manager',
        ]);

        $siteManager = User::create([
            'company_id' => $company->id,
            'name' => 'Новиков Павел (Прораб)',
            'email' => 'sm@example.com',
            'password' => Hash::make('password'),
            'role' => 'site_manager',
        ]);

        $accountant = User::create([
            'company_id' => $company->id,
            'name' => 'Соколова Анна (Бухгалтер)',
            'email' => 'accountant@example.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
        ]);

        // Создаем тестовый проект
        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Строительство жилого комплекса "Солнечный"',
            'description' => 'Строительство 16-этажного жилого дома',
            'status' => 'in_progress',
            'created_by' => $director->id,
        ]);

        // Добавляем участников проекта
        $participants = [
            [$director->id, 'director'],
            [$deputy->id, 'deputy_director'],
            [$pto->id, 'pto'],
            [$supply->id, 'supply'],
            [$projectManager->id, 'project_manager'],
            [$siteManager->id, 'site_manager'],
            [$accountant->id, 'accountant'],
        ];

        foreach ($participants as [$userId, $role]) {
            ProjectParticipant::create([
                'company_id' => $company->id,
                'project_id' => $project->id,
                'user_id' => $userId,
                'role' => $role,
            ]);
        }

        // Создаем второй проект
        $project2 = Project::create([
            'company_id' => $company->id,
            'name' => 'Реконструкция офисного здания',
            'description' => 'Капитальный ремонт офиса',
            'status' => 'created',
            'created_by' => $director->id,
        ]);

        ProjectParticipant::create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'user_id' => $director->id,
            'role' => 'director',
        ]);
    }
}