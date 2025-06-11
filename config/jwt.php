<?php

class JWTConfig {
    public static $secret_key = "sua_chave_secreta_super_forte_aqui_2025";
    public static $issuer = "api-trabalho";
    public static $audience = "api-users";
    public static $expire_time = 3600; // 1 hora
}
