<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Console\Command;

class ConfigurarCliente extends Command
{
    protected $signature = 'cms:configurar-cliente
                            {nombre : Nombre del sitio/negocio}
                            {email : Correo del administrador}
                            {password : Contraseña del administrador}';

    protected $description = 'Configura un sitio recién clonado: nombre del sitio y usuario administrador.';

    public function handle(): int
    {
        $nombre = $this->argument('nombre');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Nombre del sitio
        SiteSetting::current()->update(['site_name' => $nombre]);

        // Usuario administrador
        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Administrador', 'role' => 'admin', 'password' => $password],
        );

        $this->info("Sitio configurado: {$nombre}");
        $this->info("Administrador: {$email}");

        return self::SUCCESS;
    }
}
