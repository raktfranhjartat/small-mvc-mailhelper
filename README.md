# MailHelper

> A lightweight, universal SMTP email package for Small MVC, built on top of PHPMailer.

## Overview

MailHelper decouples your email engine from your application's content. Instead of hardcoding text templates inside your plugin, you design your emails using your framework's native views. MailHelper handles the rest—ensuring secure SMTP delivery and bulletproof character encoding.

## Features

* **Zero File-System Coupling:** Completely independent from your main application's directory structure.
* **Secure SMTP Delivery:** Native integration with PHPMailer to handle modern SMTP requirements (TLS/SSL).
* **Encoding Safeguards:** Forces UTF-8 and Base64 encoding so that special characters never break in transit.
* **Dynamic Reply-To:** Easily inject user email addresses for contact forms, allowing direct replies from email clients.

---

## Configuration

Place your SMTP credentials in a dedicated configuration file within your application. The package expects a clean associative array:

```php
<?php
// config/smtp.php

return [
    'smtp' => [
        'host'       => 'smtp.yourmailserver.com',
        'username'   => 'noreply@yourdomain.com',
        'password'   => 'your_secret_password',
        'encryption' => 'tls',
        'port'       => 587,
        'from_email' => 'noreply@yourdomain.com',
        'from_name'  => 'My Awesome App'
    ]
];
```

---

## Usage Guide

### 1. Create a View Template
Design your email layout as a standard PHP file inside your application's view folder. You can use native PHP variables just like a normal webpage layout:

```php
<div style="font-family: sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
    <h2 style="color: #004d4d;">Hello!</h2>
    <p>We received a request to reset your password.</p>
    <p>Click the link below to proceed with the reset:</p>
    <p><a href="<?php echo $resetLink; ?>"><?php echo $resetLink; ?></a></p>
    <hr style="border: 0; border-top: 1px solid #eee;">
    <small style="color: #666;">If you did not request this, you can safely ignore this email.</small>
</div>
```

### 2. Add a Renderer to your BaseController
To turn your view files into raw HTML strings, add a lightweight rendering method to your core controller utilizing PHP's output buffering:

```php
// app/core/BaseController.php
protected function renderEmailTemplate(string $template, array $data = []): string 
{
    extract($data);
    ob_start();
    include __DIR__ . "/../views/emails/" . $template . ".php";
    return ob_get_clean();
}
```

### 3. Initialize and Send
Load your configuration, compile your email body using the template renderer, and pass it directly to the `MailHelper` class inside your controller actions:

```php
<?php

namespace App\Controllers;

use Raktfranhjartat\MailHelper;

class AuthController extends BaseController
{
    public function forgotPassword()
    {
        // Fetch credentials from the application config
        $config = require __DIR__ . '/../../config/smtp.php';

        // Instantiate the MailHelper
        $mailer = new MailHelper($config['smtp']);

        // Render the email layout with dynamic parameters
        $htmlBody = $this->renderEmailTemplate('reset', [
            'resetLink' => 'https://yourdomain.com/reset?token=xyz123'
        ]);

        // Execute delivery
        $success = $mailer->send('user@example.com', 'Password Reset Request', $htmlBody);
    }
}
```

### 4. Handling Inbound Contact Forms
When processing a user submission from a contact page, you want your administrators to be able to reply directly to the sender. Pass the visitor's email address as the optional fourth argument to assign a custom `Reply-To` header:

```php
$htmlBody = $this->renderEmailTemplate('contact_form', ['message' => $_POST['message']]);

// Delivers to admin, but hits the user's inbox on reply
$mailer->send('admin@yourdomain.com', 'New Web Submission', $htmlBody, $_POST['user_email']);
```

---

## License

This component is open-source software licensed under the MIT license.
```