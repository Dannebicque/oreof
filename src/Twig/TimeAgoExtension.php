<?php

namespace App\Twig;

use DateMalformedStringException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeAgoExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_ago', $this->timeAgo(...)),
        ];
    }

    /**
     * @throws DateMalformedStringException
     */
    public function timeAgo(?\DateTimeInterface $date): string
    {
        if ($date === null) {
            return '';
        }

        $now = new \DateTimeImmutable('now', $date->getTimezone());
        $diff = $now->getTimestamp() - $date->getTimestamp();

        if ($diff < 5) {
            return "à l'instant";
        }

        $units = [
            'année' => 365 * 24 * 3600,
            'mois' => 30 * 24 * 3600,
            'semaine' => 7 * 24 * 3600,
            'jour' => 24 * 3600,
            'heure' => 3600,
            'minute' => 60,
            'seconde' => 1,
        ];

        foreach ($units as $name => $seconds) {
            if ($diff >= $seconds) {
                $count = (int)floor($diff / $seconds);
                // pluriel (mois reste identique)
                $label = $name;
                if ($count > 1 && $name !== 'mois') {
                    $label .= 's';
                }
                return "il y a {$count} {$label}";
            }
        }

        return "à l'instant";
    }
}
