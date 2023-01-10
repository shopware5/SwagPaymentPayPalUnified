<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PhpStan\CustomRules;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Param>
 */
class CrudServiceTest implements Rule
{
    /**
     * {@inheritdoc}
     */
    public function getNodeType(): string
    {
        return Param::class;
    }

    /**
     * {@inheritdoc}
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Param) {
            return [];
        }

        if (empty($node->type->parts)) {
            return [];
        }

        if (\in_array('CrudService', $node->type->parts)) {
            return [
                RuleErrorBuilder::message(
                    'CrudService should not used as type in function declaration. Please remove typehint and use annotation instead. See file: \SwagPaymentPayPalUnified\Setup\Versions\UpdateTo600.'
                )->build(),
            ];
        }

        if (\in_array('CrudServiceInterface', $node->type->parts)) {
            return [
                RuleErrorBuilder::message(
                    'CrudServiceInterface should not used as type in function declaration. Please remove typehint and use annotation instead. See file: \SwagPaymentPayPalUnified\Setup\Versions\UpdateTo600.'
                )->build(),
            ];
        }

        return [];
    }
}
