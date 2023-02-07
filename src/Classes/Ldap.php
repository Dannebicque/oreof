<?php

namespace App\Classes;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Ldap
{
    private \Symfony\Component\Ldap\Ldap $ds;

    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function connect(): void
    {
        $this->ds = \Symfony\Component\Ldap\Ldap::create('ext_ldap',
            ['connection_string' => $this->parameterBag->get('LDAP_HOST'), 'version' => 3]);
        $this->ds->bind($this->parameterBag->get('LDAP_LOGIN'), $this->parameterBag->get('LDAP_PASSWORD'));
    }

    public function getUsername(?string $email): ?string
    {
        $this->connect();

        $query = $this->ds->query($this->parameterBag->get('LDAP_BASE_DN'), '(mail='.$email.')',
            ['filter' => ['uid']]);
        $results = $query->execute()->toArray();

        if (1 === count($results)) {
            return $results[0]->getAttribute('uid')[0];
        }

        return null;
    }

    public function getDatas(?string $email)
    {
        $this->connect();

        $query = $this->ds->query($this->parameterBag->get('LDAP_BASE_DN'), '(mail='.$email.')',
            ['filter' => ['uid','sn', 'givenName']]);
        $results = $query->execute()->toArray();
        $t = [];
        if (1 === count($results)) {
            $t['username'] = $results[0]->getAttribute('uid')[0];
            $t['nom'] = $results[0]->getAttribute('sn')[0];
            $t['prenom'] = $results[0]->getAttribute('givenName')[0];

            return $t;
        }

        return null;
    }
}
