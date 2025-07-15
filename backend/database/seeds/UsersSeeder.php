<?php
use Phinx\Seed\AbstractSeed;

class UsersSeeder extends AbstractSeed
{
    public function run(): void
    {
        $password = password_hash('password', PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');

        $data = [];

        // 1 Admin
        $data[] = [
            'name' => 'Superadmin',
            'matric_number' => 'ADM001',
            'email' => 'admin@gradewise.com',
            'password' => $password,
            'role' => 'admin',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 1 Advisor
        $data[] = [
            'name' => 'Prof. Idris Bin Rahman',
            'matric_number' => 'A20001',
            'email' => 'idris@advisor.com',
            'password' => $password,
            'role' => 'advisor',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 1 Lecturer
        $data[] = [
            'name' => 'Dr. Noraini Binti Ismail',
            'matric_number' => 'L10001',
            'email' => 'noraini@lecturer.com',
            'password' => $password,
            'role' => 'lecturer',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 20 Students
        $students = [
            'Ahmad Zulkarnain', 'Nur Aisyah', 'Muhammad Faiz', 'Siti Khadijah', 'Mohd Firdaus',
            'Ainul Mardhiah', 'Muhammad Danish', 'Farah Nabilah', 'Amirul Hakim', 'Nurul Huda',
            'Syafiq Iqmal', 'Hafizah Azman', 'Nabil Firhan', 'Balqis Zahrah', 'Muhammad Irfan',
            'Aisyah Humaira', 'Iqbal Harith', 'Fatimah Zahra', 'Azim Hakimi', 'Nadiah Farzana'
        ];

        foreach ($students as $index => $name) {
            $studentNum = str_pad($index + 1, 5, '0', STR_PAD_LEFT);
            $data[] = [
                'name' => $name,
                'matric_number' => "S$studentNum",
                'email' => strtolower(str_replace(' ', '', $name)) . "@student.com",
                'password' => $password,
                'role' => 'student',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->table('users')->insert($data)->saveData();
    }
}
