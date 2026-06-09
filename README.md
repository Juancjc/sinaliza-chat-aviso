<div align="center">

# 💬 Chat Aviso

### Grupos, conversas e avisos importantes em um só lugar

<img src="https://readme-typing-svg.demolab.com?font=Fira+Code&weight=600&size=22&pause=1000&color=41B883&center=true&vCenter=true&width=700&lines=Crie+grupos+e+converse+com+sua+turma+%F0%9F%92%AC;Envie+avisos+por+e-mail+%F0%9F%93%A8;Compartilhe+convites+tempor%C3%A1rios+%F0%9F%94%97;Feito+com+Amor+e+Vibe+Coding+%E2%9C%A8" alt="Apresentação animada do Chat Aviso" />

<br />

[![Laravel](https://img.shields.io/badge/Laravel_13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js_3-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![PrimeVue](https://img.shields.io/badge/PrimeVue_4-41B883?style=for-the-badge&logo=primevue&logoColor=white)](https://primevue.org)
[![Laravel Reverb](https://img.shields.io/badge/Laravel_Reverb-WebSockets-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/docs/reverb)

[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![TypeScript](https://img.shields.io/badge/TypeScript-3178C6?style=for-the-badge&logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![Vite](https://img.shields.io/badge/Vite_8-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vite.dev)
[![Pest](https://img.shields.io/badge/Testado_com_Pest-F59E0B?style=for-the-badge&logo=php&logoColor=white)](https://pestphp.com)

![Status](https://img.shields.io/badge/status-em_melhoria_cont%C3%ADnua-blue?style=flat-square)
![Uso](https://img.shields.io/badge/uso-livre-brightgreen?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-%3E%3D_8.3-777BB4?style=flat-square&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-padr%C3%A3o-003B57?style=flat-square&logo=sqlite&logoColor=white)

</div>

---

## 🌟 Sobre o projeto

O **Chat Aviso** é uma plataforma simples, bonita e responsiva para organizar a comunicação entre administradores e alunos.

Crie grupos, converse pelo chat, envie avisos por e-mail, compartilhe links temporários e deixe cada usuário escolher seu próprio emoji de perfil.

Ideal para:

- 🎓 Escolas, cursos e turmas
- 🧑‍🏫 Professores e instrutores
- 👥 Comunidades e equipes
- 🏢 Grupos internos e treinamentos
- 🚀 Qualquer projeto que precise de comunicação organizada

> [!NOTE]
> O projeto está em **melhoria contínua**. Novas funcionalidades, ajustes visuais e melhorias técnicas podem ser adicionados com o tempo.

## ✨ Funcionalidades

<table>
<tr>
<td width="50%" valign="top">

### 👑 Administradores

- ✅ Criar, editar e excluir grupos
- 👥 Adicionar e remover alunos
- 🔗 Gerar links temporários de convite
- ⏳ Escolher a validade dos links
- 🚫 Revogar convites ativos
- 💬 Participar dos chats
- ⚡ Receber mensagens em tempo real
- 🔔 Ver notificações de novos chats
- 📧 Enviar avisos por e-mail
- 🛡️ Gerenciar somente os próprios grupos

</td>
<td width="50%" valign="top">

### 🎓 Alunos

- 👀 Visualizar somente seus grupos
- 🔗 Entrar por convite temporário
- 💬 Conversar com a turma
- ⚡ Receber mensagens em tempo real
- 🔔 Acompanhar mensagens e avisos não lidos pelo sininho
- 📢 Ler avisos importantes em modais sequenciais
- 😀 Escolher qualquer emoji como avatar
- 🔎 Pesquisar emojis por categoria
- 🎨 Alterar o perfil quando quiser
- 🔐 Acessar apenas grupos autorizados

</td>
</tr>
</table>

## 🎨 Interface com PrimeVue

O projeto utiliza **PrimeVue** para entregar componentes acessíveis, consistentes e agradáveis:

| Componente | Utilização |
| --- | --- |
| `Button` | Ações, navegação, envio e remoção |
| `Card` | Organização visual das páginas e grupos |
| `DataTable` e `Column` | Listagem de participantes |
| `Select` | Seleção de alunos e duração dos convites |
| `InputText` | Campos e links compartilháveis |
| `Textarea` | Mensagens do chat e avisos |
| `Message` | Alertas e informações importantes |
| `Popover` | Seletor completo de emojis |
| `Avatar` | Emojis dos participantes |
| `Toast` | Notificações em tempo real com grupo, remetente e resumo |
| `Popover` | Lista de mensagens e avisos não lidos no sininho |
| `Dialog` | Leitura obrigatória e sequencial dos avisos |
| `Tooltip` | Explicações rápidas das ações |

O seletor de avatar suporta **todos os emojis Unicode disponíveis no dispositivo**, incluindo busca, categorias, bandeiras, favoritos, tons de pele e emojis compostos. 🌈

## 🧰 Tecnologias utilizadas

| Camada | Tecnologia |
| --- | --- |
| Backend | Laravel 13 e PHP 8.3+ |
| Frontend | Vue 3 e TypeScript |
| Navegação SPA | Inertia.js 3 |
| Componentes | PrimeVue 4 |
| Estilização | Tailwind CSS 4 |
| Build | Vite 8 |
| Autenticação | Laravel Fortify |
| Banco padrão | SQLite |
| E-mails | Laravel Mail |
| Tempo real | Laravel Reverb, Laravel Echo e WebSockets |
| Broadcasting | Canais privados por usuário |
| Filas | Laravel Queue com driver de banco de dados |
| Testes | Pest |
| Qualidade | Laravel Pint, ESLint, Prettier e Vue TSC |

## 🧭 Como funciona

```mermaid
flowchart LR
    A["👑 Admin cria um grupo"] --> B["🔗 Compartilha convite temporário"]
    B --> C["🎓 Aluno entra no grupo"]
    C --> D["💬 Turma conversa no chat"]
    D --> G["📥 Fila processa o broadcast"]
    G --> H["⚡ Reverb envia por WebSocket"]
    H --> I["🔔 Toast ou atualização do chat aberto"]
    A --> E["📧 Admin envia avisos"]
    E --> F["📨 Alunos recebem por e-mail"]
```

## 🚀 Instalação

### Requisitos

- PHP 8.3 ou superior
- Composer
- Node.js e npm

### Preparar o projeto

```bash
git clone <url-do-repositorio>
cd chat-aviso

composer install
npm install
```

### Configurar o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### Criar o banco e os dados de demonstração

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
```

### Iniciar o desenvolvimento

```bash
composer dev
```

O comando inicia o Laravel, o worker da fila, o servidor Reverb e o Vite juntos. Pronto! Abra o endereço configurado em `APP_URL`. 🎉

## ⚡ Mensagens em tempo real

Cada mensagem salva dispara um evento `ShouldBroadcast`, processado pela fila e entregue pelo **Laravel Reverb**. O frontend usa **Laravel Echo** para ouvir um canal privado autenticado.

- Somente o admin criador e os alunos participantes recebem a mensagem.
- Se o grupo estiver aberto, o chat é atualizado diretamente.
- Se outro grupo ou página estiver aberta, um `Toast` PrimeVue aparece no topo central.
- O toast mostra o grupo, quem enviou, o avatar emoji e um resumo da mensagem.
- A própria mensagem do usuário não gera notificação para ele.
- O sininho mantém mensagens e avisos não lidos persistidos no banco.
- Abrir um chat marca as mensagens daquele grupo como lidas.
- Alunos recebem avisos em modais sequenciais até confirmar a leitura de todos.
- Administradores recebem mensagens não lidas, mas nunca recebem avisos.

Em produção, mantenha estes processos ativos:

```bash
php artisan queue:work
php artisan reverb:start
```

## 🔑 Contas de demonstração

| Perfil | E-mail | Senha |
| --- | --- | --- |
| 👑 Administrador | `admin@example.com` | `password` |
| 🎓 Aluno | `ana@example.com` | `password` |

## 📧 Configuração de e-mails

Por padrão, os avisos enviados são gravados no log:

```text
storage/logs/laravel.log
```

Para enviar e-mails reais, configure as variáveis `MAIL_*` no arquivo `.env` com os dados do seu serviço SMTP.

## 🧪 Testes e qualidade

Execute a suíte completa:

```bash
composer test
```

Valide o frontend:

```bash
npm run lint:check
npm run types:check
npm run format:check
npm run build
```

## 🗺️ Ideias para o futuro

- 📎 Envio de arquivos e imagens
- 📱 Melhorias para dispositivos móveis
- 📊 Painel com métricas dos grupos
- 📨 Filas para envio de grandes quantidades de e-mails

## 💚 Uso livre

Este sistema é **livre para uso**.

Você pode estudar, modificar, adaptar, distribuir e utilizar o projeto como quiser, inclusive em projetos:

- Pessoais
- Acadêmicos
- Comunitários
- Comerciais

Use, transforme, compartilhe e deixe o Chat Aviso com a sua cara. Contribuições, ideias e melhorias são sempre bem-vindas.

---

<div align="center">

## 💖 Feito com Amor e Vibe Coding ✨

<sub>Construindo, aprendendo e melhorando uma ideia de cada vez.</sub>

</div>
