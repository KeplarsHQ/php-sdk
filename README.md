# Keplars Email SDK for PHP

Official PHP SDK for the Keplars Email API - modern transactional email service with priority-based delivery.

## Installation

```bash
composer require keplars/email-sdk
```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use Keplars\Email\Client;

$client = new Client('kms_<workspaceId>.live_<secret>');

$result = $client->emails->sendInstant([
    'to' => 'user@example.com',
    'from' => 'noreply@yourdomain.com',
    'subject' => 'Your verification code is 123456',
    'html' => '<p>Your verification code is <strong>123456</strong></p>'
]);

echo $result['data']['job_id'];
```

### API Key Types

| Type | Format | Used for |
|---|---|---|
| Regular | `kms_<id>.live_<secret>` | Email sending |
| Admin | `kms_<id>.adm_<secret>` | Contacts, audiences, automations, domains |

## Email Sending

### Priority Levels

| Method | Delivery | Use case |
|---|---|---|
| `sendInstant` | 0–5 sec | OTPs, login codes, critical alerts |
| `sendHigh` | 0–30 sec | Transactional, notifications |
| `sendAsync` / `send` | 0–5 min | General transactional |
| `sendBulk` | Idle | Newsletters, marketing |

### Send with Recipients

```php
$result = $client->emails->sendHigh([
    'to' => [
        ['email' => 'user@example.com', 'name' => 'John Doe']
    ],
    'from' => 'noreply@yourdomain.com',
    'subject' => 'Order Confirmation',
    'html' => '<p>Your order has been confirmed</p>',
    'cc' => [
        ['email' => 'manager@example.com']
    ]
]);
```

### Response Shape

```php
$result['success'];           // true
$result['message'];           // 'Email queued'
$result['data']['job_id'];    // 'job_abc123'
$result['data']['priority'];  // 'high'
```

### Send with Template

```php
$result = $client->emails->send([
    'to' => 'user@example.com',
    'from' => 'noreply@yourdomain.com',
    'subject' => 'Password Reset',
    'template_id' => 'tpl_reset_password',
    'template_data' => [
        'name' => 'John',
        'reset_link' => 'https://example.com/reset/abc'
    ]
]);
```

### Schedule Email

```php
$result = $client->emails->schedule([
    'to' => 'user@example.com',
    'from' => 'newsletter@yourdomain.com',
    'subject' => 'Your weekly digest',
    'html' => '<p>Here is your weekly digest...</p>',
    'scheduled_for' => '2026-06-01T09:00:00Z',
    'priority' => 'bulk'
]);
```

## Contacts (Admin API Key Required)

```php
$adminClient = new Client('kms_<workspaceId>.adm_<secret>');

$adminClient->contacts->add([
    'email' => 'user@example.com',
    'name' => 'John Doe',
    'audience_id' => 'aud_abc123'
]);

$contact = $adminClient->contacts->get('user@example.com');

$contacts = $adminClient->contacts->list('aud_abc123', 1, 20);

$adminClient->contacts->update('user@example.com', ['name' => 'Jane Doe']);

$adminClient->contacts->delete('user@example.com');
```

## Audiences (Admin API Key Required)

```php
$audience = $adminClient->audiences->create('Newsletter Subscribers', 'Main list');

$audiences = $adminClient->audiences->list(1, 20);

$audience = $adminClient->audiences->get('aud_abc123');

$adminClient->audiences->delete('aud_abc123');
```

## Automations (Admin API Key Required)

```php
$automations = $adminClient->automations->list();

$automation = $adminClient->automations->get('auto_abc123');

$adminClient->automations->enroll('auto_abc123', 'user@example.com');

$adminClient->automations->unenroll('auto_abc123', 'user@example.com');
```

## Domains (Admin API Key Required)

```php
$domain = $adminClient->domains->add('mail.yourcompany.com');

$domains = $adminClient->domains->list();

$status = $adminClient->domains->getStatus('dom_abc123');

$result = $adminClient->domains->verify('dom_abc123');

$apiKey = $adminClient->domains->createApiKey(['domain_id' => 'dom_abc123', 'name' => 'Production Key']);

$adminClient->domains->delete('dom_abc123');
```

## Framework Integration

### Laravel

```php
// config/keplars.php
return ['api_key' => env('KEPLARS_API_KEY')];
```

```php
// AppServiceProvider.php
use Keplars\Email\Client;

$this->app->singleton(Client::class, fn() => new Client(config('keplars.api_key')));
```

```php
// In a controller
use Keplars\Email\Client;

class WelcomeController extends Controller
{
    public function __construct(private readonly Client $keplars) {}

    public function send(): void
    {
        $this->keplars->emails->sendInstant([
            'to'      => 'user@example.com',
            'from'    => 'noreply@yourdomain.com',
            'subject' => 'Welcome!',
            'html'    => '<h1>Hello!</h1>',
        ]);
    }
}
```

### CodeIgniter 4

```php
// app/Config/Keplars.php
namespace Config;
use CodeIgniter\Config\BaseConfig;

class Keplars extends BaseConfig
{
    public string $apiKey = '';

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('KEPLARS_API_KEY', '');
    }
}
```

```php
// In a controller
use Keplars\Email\Client;

class EmailController extends BaseController
{
    private Client $keplars;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->keplars = new Client(config('Keplars')->apiKey);
    }

    public function send(): ResponseInterface
    {
        $this->keplars->emails->sendInstant([
            'to'      => 'user@example.com',
            'from'    => 'noreply@yourdomain.com',
            'subject' => 'Welcome!',
            'html'    => '<h1>Hello!</h1>',
        ]);

        return $this->response->setJSON(['status' => 'sent']);
    }
}
```

### Symfony

```php
// config/services.yaml
services:
  Keplars\Email\Client:
    arguments:
      $apiKey: '%env(KEPLARS_API_KEY)%'
```

```php
// In a controller
use Keplars\Email\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailController extends AbstractController
{
    public function __construct(private readonly Client $keplars) {}

    public function send(): Response
    {
        $this->keplars->emails->sendInstant([
            'to'      => 'user@example.com',
            'from'    => 'noreply@yourdomain.com',
            'subject' => 'Welcome!',
            'html'    => '<h1>Hello!</h1>',
        ]);

        return $this->json(['status' => 'sent']);
    }
}
```

### Plain PHP

```php
<?php
require 'vendor/autoload.php';

use Keplars\Email\Client;

$keplars = new Client($_ENV['KEPLARS_API_KEY']);

$keplars->emails->sendInstant([
    'to'      => 'user@example.com',
    'from'    => 'noreply@yourdomain.com',
    'subject' => 'Welcome!',
    'html'    => '<h1>Hello!</h1>',
]);
```

## Error Handling

```php
use Keplars\Email\Exceptions\AuthenticationException;
use Keplars\Email\Exceptions\RateLimitException;
use Keplars\Email\Exceptions\ValidationException;

try {
    $result = $client->emails->sendInstant([...]);
} catch (AuthenticationException $e) {
    echo 'Invalid API key';
} catch (RateLimitException $e) {
    echo 'Rate limited';
} catch (ValidationException $e) {
    echo 'Validation error: ' . $e->getMessage();
}
```
