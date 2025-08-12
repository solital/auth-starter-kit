# Solital Auth Starter Kit

This component must be used together with the Solital Framework

## Installation

You must install using composer:

```bash
composer require solital/auth-starter-kit
```

## Standard login structure 

To create a predefined login structure, use `php vinci auth:skeleton --login`

This command will create a `LoginController` class, `AuthMiddleware` middleware, templates for authentication, dashboard and predefined routes. Plus a standard user in the database.

If you want to remove this structure, use `php vinci auth:skeleton --login --remove`.

## Password recovery structure

You can create a predefined password recovery framework. To do so, use the `php vinci auth:skeleton --forgot` command.

This command creates a controller with the name `ForgotController`. With it you will have all the basis to create a password recovery system.

If you want to remove this structure, use `php vinci auth:skeleton --forgot --remove`.