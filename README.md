# revision-ten/sendinblue

## Installation

#### Install via composer

Run `composer req revision-ten/sendinblue`.

### Add the Bundle

Add the bundle to your AppKernel (Symfony 3.4.\*) or your Bundles.php (Symfony 4.\*).

Symfony 3.4.\* /app/AppKernel.php:
```PHP
new \RevisionTen\Sendinblue\Sendinblue\Bundle(),
```

Symfony 4.\* /config/bundles.php:
```PHP
RevisionTen\Sendinblue\SendinblueBundle::class => ['all' => true],
```

### Configuration

Configure the bundle:

```YAML
# sendinblue example config.
sendinblue:
    api_key: 'XXXXXXXXXXXXXXXXXXXXXXX-us5' # Your sendinblue api key.
    campaigns:
        dailyNewsletterCampagin:
            list_id: 12345 # Id of your newsletter list.
```

### Usage

Use the SendinblueService to subscribe users.

Symfony 3.4.\* example:
```PHP
$sendinblueService = $this->container->get(SendinblueService::class);

try {
    $subscribed = $sendinblueService->subscribe('dailyNewsletterCampagin', 'visitor.email@domain.tld', 'My Website', [
        'FNAME' => 'John',
        'LNAME' => 'Doe',
    ]);
} catch (Exception $e) {
    // ...
}
```

Or unsubscribe users:
```PHP
$sendinblueService = $this->container->get(SendinblueService::class);

$unsubscribed = $sendinblueService->unsubscribe('dailyNewsletterCampagin', 'visitor.email@domain.tld');
```
