<?php

namespace Gurman\ChainCommandBundle\Manager;

class CommandsManager
{
    private array $chains = [];

    public function setChains(array $chains): self
    {
        $this->chains = $chains;

        return $this;
    }

    public function getMembersForMain(string $mainCommandName): ?array
    {
        foreach ($this->chains as $chain) {
            if ($chain['main_command'] === $mainCommandName) {
                return $chain['members'];
            }
        }

        return null;
    }

    public function getMainForMember(string $memberCommandName): ?string
    {
        foreach ($this->chains as $chain) {
            foreach ($chain['members'] as $member)
            if ($memberCommandName === $member['command']) {
                return $chain['main_command'];
            }
        }

        return null;
    }
}
