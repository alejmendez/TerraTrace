<?php

return [
    'login' => [
        'title' => 'Iniciar Sesión',
        'form' => [
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'remember_me' => 'Recuerdame',
            'submit' => 'Ingresar',
        ],
        'links' => [
            'restore_password' => 'Restablecer contraseña',
        ],
        'errors' => [
            'unauthorized' => 'Correo electrónico o Contraseña incorrectos, intentelo nuevamente.',
        ],
    ],
    'confirmPassword' => [
        'title' => 'Confirmar contraseña',
        'subtitle' => 'Esta es una zona segura de la aplicación. Confirme su contraseña antes de continuar.',
        'form' => [
            'password' => 'Contraseña',
            'submit' => 'Confirmar',
        ],
    ],
    'resetPassword' => [
        'title' => 'Restablecer contraseña',
        'form' => [
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'confirm_password' => 'Confirmar contraseña',
            'submit' => 'Restablecer contraseña',
        ],
    ],
    'register' => [
        'title' => 'Registro',
        'form' => [
            'name' => 'Nombre',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'confirm_password' => 'Confirmar contraseña',
            'submit' => 'Registrar',
        ],
        'already_registered' => '¿Ya se ha registrado?',
    ],
    'forgotPassword' => [
        'title' => 'Olvidé mi contraseña',
        'subtitle' => '¿Ha olvidado su contraseña? No se preocupe. Díganos su dirección de correo electrónico y le enviaremos un enlace para restablecer la contraseña. que le permitirá elegir una nueva.',
        'form' => [
            'email' => 'Correo electrónico',
            'submit' => 'Enlace de restablecimiento de contraseña por correo electrónico',
        ],
    ],
    'verifyEmail' => [
        'title' => 'Verificación del correo electrónico',
        'subtitle' => 'Gracias por registrarte. Antes de empezar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar? Si no lo has recibido, estaremos encantados de enviarte otro.',
        'alert' => 'Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionó durante el registro.',
        'form' => [
            'submit' => 'Reenviar correo de verificación',
            'logout' => 'Cerrar sesión',
        ],
    ],
];
