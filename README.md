# Inventario de Calzado

Sistema web de inventario y ventas para una tienda de calzado, hecho en **PHP y MySQL**. Proyecto de título de Técnico de Nivel Superior en Computación e Informática (UTFSM).

## Funcionalidades

- Inicio de sesión con roles (administrador y usuarios)
- Gestión de usuarios y grupos
- Gestión de productos y categorías, con imágenes
- Productos descontinuados (se pueden restaurar)
- Registro, edición y eliminación de ventas
- Reportes de ventas diarias, mensuales y por rango de fechas, exportables a PDF
- Panel de control con resumen general

## Tecnologías

- PHP
- MySQL / MariaDB
- HTML, CSS y JavaScript (jQuery)
- [Dompdf](https://github.com/dompdf/dompdf) para generar PDF
- XAMPP como servidor local

## Instalación (XAMPP)

1. Descarga o clona el repositorio dentro de `C:\xampp\htdocs\`:
   ```bash
   git clone https://github.com/agustin615/Inventario-calzado.git
   ```
2. Abre XAMPP e inicia **Apache** y **MySQL**.
3. Entra a `http://localhost/phpmyadmin`, crea una base de datos llamada `bd` e importa el archivo `bd.sql`.
4. Copia `config.example.php` a `includes/config.php` y ajusta los datos si es necesario (por defecto sirve para XAMPP).
5. Abre `http://localhost/Inventario-calzado/` en el navegador.

## Acceso de prueba

| Usuario | Contraseña |
|---------|------------|
| admin   | admin
> Si vas a usar esto fuera de tu computador, cambia las contraseñas y los datos de `config.php`.

## Estructura

```
├── includes/    Configuración, base de datos, sesión y funciones
├── layouts/     Cabecera, pie y menús
├── libs/        CSS, JS y Dompdf
├── uploads/     Imágenes de productos y usuarios
├── bd.sql       Esquema y datos iniciales
└── *.php        Páginas del sistema
```

## Autor

Agustín Uribe, Viña del Mar, Chile.
