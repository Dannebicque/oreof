<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/EtatProcessMentionEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/07/2024 13:12
 */

namespace App\Enums;

enum EtatProcessMentionEnum: string
{
    case NON_FAIT = 'NON_FAIT';
    case WIP = 'WIP';
    case RESERVE = 'RESERVE';
    case COMPLETE = 'COMPLETE';

    public function color(): string
    {
        return match ($this) {
            self::NON_FAIT => 'muted',
            self::WIP => 'info',
            self::RESERVE => 'warning',
            self::COMPLETE => 'success',
        };
    }
}
