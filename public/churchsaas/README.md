# Diaconia — Sistema SaaS de Gestão de Igrejas

Plataforma web **multi-tenant (multi-igreja)** para administração pastoral, secretaria,
tesouraria e **diaconia**, construída em **Symfony 7 / PHP 8.3+**, com **PostgreSQL** ou
**MariaDB**, interface **Bootstrap 5** responsiva e **API REST**.

Cada igreja é um *tenant* isolado: os dados de uma congregação nunca se misturam com os de
outra, garantido no nível do banco por um filtro Doctrine automático.

---

## 1. Requisitos

- PHP 8.3 ou superior (extensões: `pdo_pgsql` ou `pdo_mysql`, `intl`, `ctype`, `iconv`)
- Composer 2
- PostgreSQL 14+ **ou** MariaDB 10.6+
- Symfony CLI (opcional, recomendado para desenvolvimento)

## 2. Instalação

```bash
# 1. Dependências
composer install

# 2. Configure o banco em .env (ou .env.local)
#    Já vem pronto para PostgreSQL; há uma linha comentada para MariaDB.

# 3. Crie o banco e o esquema
php bin/console doctrine:database:create
php bin/console doctrine:migrations:diff      # gera a migração inicial a partir das entidades
php bin/console doctrine:migrations:migrate

#    (alternativa rápida em dev, sem migração:)
#    php bin/console doctrine:schema:create

# 4. Carregue dados de demonstração (uma igreja com usuários, membros, escalas etc.)
php bin/console doctrine:fixtures:load

# 5. Suba o servidor
symfony serve            # ou: php -S localhost:8000 -t public
```

Acesse `http://localhost:8000`.

## 3. Usuários de demonstração

Todos usam a senha **`senha123`** e pertencem à igreja *Betel — Sede*:

| E-mail                  | Perfil        |
|-------------------------|---------------|
| admin@betel.org         | Administrador |
| pastor@betel.org        | Pastor        |
| secretaria@betel.org    | Secretário    |
| tesouraria@betel.org    | Tesoureiro    |
| diacono@betel.org       | Diácono       |

Cada perfil enxerga apenas os módulos permitidos (ver seção 6).

---

## 4. Arquitetura multi-tenant

O isolamento por igreja é o pilar do sistema e funciona em três camadas:

1. **`TenantContext`** (`src/Tenant/`) — guarda a igreja ativa durante a requisição.
2. **`TenantSubscriber`** (`src/EventSubscriber/`) — no início de cada requisição resolve a
   igreja a partir do usuário autenticado, ativa o filtro Doctrine e, via `prePersist`,
   injeta a igreja em qualquer entidade nova automaticamente.
3. **`TenantFilter`** (`src/Tenant/`) — filtro SQL do Doctrine que adiciona
   `WHERE church_id = :tenant_id` a **toda** entidade que implementa `TenantAwareInterface`.

Consequência prática: um controller ou repositório nunca precisa filtrar manualmente por
igreja — o filtro faz isso sozinho. Para tornar uma nova entidade multi-tenant, basta:

```php
class MinhaEntidade implements TenantAwareInterface
{
    use TenantAwareTrait;   // adiciona a relação church_id
    // ...
}
```

---

## 5. Módulos e status

| # | Módulo | Entidades | Implementação |
|---|--------|-----------|----------------|
| 1 | **Cadastro de Membros** | `Member`, `Ministry` | ✅ CRUD web + API REST + upload de foto |
| 2 | Congregados e Visitantes | `Visitor` | ✅ modelo + integração/conversão |
| 3 | **Diaconia** | `Deacon`, `Schedule`, `ScheduleAssignment` | ✅ escalas, designação, presença, histórico |
| 4 | Pastoral | `PastoralAppointment`, `PrayerRequest` | ✅ modelo (agenda, aconselhamento, oração) |
| 5 | **Tesouraria** | `Transaction`, `FinancialCategory`, `Campaign` | ✅ lançamentos, dízimos, ofertas, fluxo de caixa |
| 6 | Eventos | `Event`, `EventRegistration` | ✅ modelo + inscrições |
| 7 | Escola Bíblica | `SchoolClass`, `Student`, `ClassAttendance` | ✅ modelo (turmas, alunos, frequência) |
| 8 | Comunicação | — | 🔌 pontos de integração (WhatsApp/e-mail) prontos no `.env` |
| 9 | Relatórios | (consultas) | ✅ crescimento, fluxo de caixa, diaconia |
| 10 | Painel Administrativo | Dashboard | ✅ indicadores executivos + gráficos |

✅ = pronto para uso · 🔌 = estrutura/integração preparada

Os módulos 1, 3, 5 e 10 estão **totalmente implementados** (controllers, formulários,
templates e — no caso de Membros — API REST) e servem de **referência de padrão** para
completar os demais, que já têm o modelo de dados e as regras de acesso definidos.

---

## 6. Perfis de acesso

A hierarquia está em `config/packages/security.yaml` e o controle por módulo em
`src/Security/Voter/ModuleVoter.php`:

- **Administrador** → acesso total
- **Pastor** → herda Secretário, Tesoureiro, Diácono e Líder; acesso à Pastoral
- **Secretário** → Membros, Visitantes, Eventos, Escola, Comunicação
- **Tesoureiro** → Tesouraria e Relatórios financeiros
- **Diácono** → Diaconia
- **Líder de Ministério** → Eventos e Escola do seu ministério

Nos templates: `{% if is_granted('MODULE_DIACONIA') %}`. Nos controllers:
`#[IsGranted('MODULE_TREASURY')]`.

---

## 7. API REST

Exemplo completo em `src/Controller/Api/MemberApiController.php`:

```
GET    /api/membros           lista (aceita ?q= e ?status=)
GET    /api/membros/{id}      detalhe
POST   /api/membros           cria (JSON)
PUT    /api/membros/{id}      atualiza
DELETE /api/membros/{id}      remove
```

Autenticação: firewall `api` com **HTTP Basic** nesta base (funciona de imediato). Para
produção, acople `lexik/jwt-authentication-bundle` — o firewall `api` já está isolado e
`stateless`. O filtro multi-tenant também vale para a API: cada credencial só acessa os
dados da sua igreja.

---

## 8. Comunicação (módulo 8)

O `.env` já contém as variáveis `WHATSAPP_API_URL` / `WHATSAPP_API_TOKEN` e o `MAILER_DSN`
do Symfony Mailer. A implementação de envio é um serviço a plugar em `src/Service/`
(um `CommunicationService` que consome a API do provedor de WhatsApp escolhido e usa o
Mailer para e-mail marketing/avisos).

---

## 9. Estrutura de pastas

```
src/
├── Controller/Web/     # telas (login, dashboard, membros, diaconia, tesouraria)
├── Controller/Api/     # API REST
├── Entity/             # 18 entidades cobrindo os 10 módulos
├── Repository/         # consultas (busca, totais, fluxo de caixa, escalas)
├── Form/               # formulários Symfony
├── Security/Voter/     # controle de acesso por módulo
├── Tenant/             # infraestrutura multi-tenant
├── EventSubscriber/    # resolução do tenant por requisição
└── DataFixtures/       # dados de demonstração
templates/              # Bootstrap 5, responsivo (Twig)
config/                 # doctrine, security, services, rotas
```

---

## 10. Próximos passos sugeridos

- Completar telas de Pastoral, Eventos, Escola e Comunicação (mesmo padrão de Membros).
- Trocar HTTP Basic por JWT na API.
- Painel de super-admin para provisionar novas igrejas (onboarding SaaS) e planos.
- Exportação de relatórios em PDF/Excel para prestação de contas.
- Fila (Messenger) para disparos de WhatsApp/e-mail em massa.

Licença: MIT.
