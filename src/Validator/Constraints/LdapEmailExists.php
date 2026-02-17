<?php
// src/Validator/Constraints/LdapEmailExists.php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

//todo: pas utile ?
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER)]
final class LdapEmailExists extends Constraint
{
    public string $message = 'Aucun compte trouvé dans le LDAP pour l’email "{{ email }}".';

    public function __construct(
        public readonly ?string $connection = null,
        ?string                 $message = null,
        mixed                   $options = null,
        ?array                  $groups = null,
        mixed                   $payload = null
    )
    {
        dump($options);
        parent::__construct($options ?? [], $groups, $payload);

        if ($message !== null) {
            $this->message = $message;
        }
    }
}
