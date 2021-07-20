<?php
declare(strict_types=1);

namespace Iit\HyLib\Model;

use Hyperf\Database\Commands\Ast\ModelUpdateVisitor as Visitor;
use Iit\HyLib\CastsAttributes\ArraySearch;

class ModelUpdateVisitor extends Visitor
{
    /**
     * @param string $type
     * @param string|null $cast
     * @return string|null
     */
    protected function formatPropertyType(string $type, ?string $cast): ?string
    {
        $cast = parent::formatPropertyType($type, $cast);
        if ($cast === ArraySearch::class) {
            return 'array';
        }
        return $cast;
    }
}
