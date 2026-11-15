<?php

namespace App\Services;

use App\Exceptions\InvalidSecretSantaException;

class SecretSantaRandomizer
{
    /**
     * @param  int[]  $participants
     * @param  array<int, array{0: int, 1: int}>|null  $exclusions  pairs that must not draw each other
     * @return array<int, int>
     *
     * @throws InvalidSecretSantaException
     */
    public static function randomize(
        array $participants,
        ?array $exclusions = null,
    ): array {
        $exclusions ??= [];
        $attempts = 0;

        do {
            $attempts++;

            if ($attempts > 500) {
                throw new InvalidSecretSantaException('Maximum number of attempts reached.');
            }

            $shuffledParticipants = static::shuffle($participants);

            $newParticipants = $shuffledParticipants;
            $first = array_shift($newParticipants);
            $newParticipants[] = $first;
            $newParticipants = array_values($newParticipants);

            $matches = array_combine($shuffledParticipants, $newParticipants);
        } while (static::containsInvalidMatches($matches, $exclusions));

        return $matches;
    }

    private static function shuffle(array $participants): array
    {
        shuffle($participants);

        return $participants;
    }

    private static function containsInvalidMatches(array $matches, array $exclusions): bool
    {
        $excludedPairs = [];

        foreach ($exclusions as [$first, $second]) {
            $excludedPairs["{$first}:{$second}"] = true;
            $excludedPairs["{$second}:{$first}"] = true;
        }

        foreach ($matches as $from => $to) {
            if (isset($excludedPairs["{$from}:{$to}"])) {
                return true;
            }
        }

        return false;
    }
}
