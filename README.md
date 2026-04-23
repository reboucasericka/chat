# Chat

Sistema de chat interno desenvolvido com Laravel + Vue (Inertia), inspirado na experiência de ferramentas como Messenger e Campfire.  
O foco do projeto é oferecer comunicação rápida entre utilizadores, com salas e conversas diretas em uma interface simples e limpa.

## Tecnologias usadas

- Laravel 12
- Vue 3 + Inertia
- MySQL
- Tailwind CSS

## Funcionalidades

- Chat direto entre utilizadores
- Salas de chat
- Envio de ficheiros
- Emojis
- Reações
- Presença online

## Instalação

```bash
git clone <url-do-repositorio>
cd chat

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate --seed

npm run dev
```

## Screenshots

> Adicionar screenshots aqui:

- `docs/screenshots/home.png`
- `docs/screenshots/chat.png`
- `docs/screenshots/rooms.png`

## Autor

Projeto desenvolvido por **Ericka Rebouças**.
