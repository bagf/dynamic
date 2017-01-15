# Dynamic Library
Dynamic/Hybrid class generator library which can work in many situations. This library can implemented where existing classes or objects that need to be evolved at a runtime.

## Installation
```sh
composer require bagf/dynamic
```

## Example

@todo More documentation

```php
        $profile = \Bagf\Dynamic\Builder::fromInstance($profile)
            ->implement(SendInvitation::class)
            ->shareTrait(InviteUsers::class)
            ->instance();
```
