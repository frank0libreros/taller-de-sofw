<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Datos de prueba
    public static function getTestData()
    {
        return [
            [
                'id' => 1,
                'name' => 'Administrador',
                'email' => 'admin@tienda.com',
                'password' => Hash::make('12345678'),
            ],
            [
                'id' => 2,
                'name' => 'Vendedor 1',
                'email' => 'vendedor1@tienda.com',
                'password' => Hash::make('12345678'),
            ],
        ];
    }

    public function findByEmail($email)
    {
        $users = self::getTestData();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public function findById($id)
    {
        $users = self::getTestData();
        foreach ($users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }
}