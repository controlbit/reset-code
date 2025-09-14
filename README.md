# ResetCode Bundle

The ResetCode Bundle provides a simple, database-backed way to generate and manage temporary reset codes (e.g., verification codes, one-time passwords, password reset tokens).
It integrates with Doctrine DBAL and automatically manages schema creation via Doctrine schema events.

Features

🔑 Generate short-lived reset codes with configurable size and TTL.<br/>
🗄️ Multiple tables supported, each with its own configuration.<br/>
🎭 Aliases for services to easily access different code managers.<br/>
⚡ Automatic Doctrine schema updates via event listeners.<br/>

## Installation
```bash
composer require controlbit/reset-code
```

Put in Bundles file of your Symfony project:
```php
return [
    ControlBit\ResetCode\ResetCodeBundle::class => ['all' => true],
];
```

## Configuration

```yaml
# config/packages/reset_code.yaml
reset_code:
  enabled: true

  tables:
    - name: "password_reset"
      alias: "user_reset"
      connection_name: "default"
      code_size: 6
      ttl: 300
      timeout_to_clear_oldest_ms: 250
      allow_subject_duplicates: false

    - name: "phone_verification"
      alias: null
      connection_name: "readonly"
      code_size: 4
      ttl: 120
      timeout_to_clear_oldest_ms: 500
      allow_subject_duplicates: true

```

## Usage

Each configured table automatically registers a ResetCodeManager service.<br/>
Service IDs are built as:<br/>

reset_code.{name}<br/>
reset_code.{alias} (if alias is set)<br/>
reset_code.default (first configured table, acts as fallback)<br/>
<br/>
You can also type-hint `ControlBit\ResetCode\Service\ResetCodeManager` in your services — it will resolve to the default manager.

### Example of generating a code:

```php
use ControlBit\ResetCode\Service\ResetCodeManager;

class PasswordResetService
{
    public function __construct(private ResetCodeManager $resetCodeManager) {}

    public function requestReset(string $userId): string
    {
        return $this->resetCodeManager->generateCode($userId);
    }
}
```

If you configured multiple tables:
```php
$phoneResetManager = $container->get('reset_code.phone_verification');
$code = $phoneResetManager->generateCode($phoneNumber);
```

## Database migrations
Generate migration or do schema update:
```bash
php bin/console doctrine:schema:update --force
```
