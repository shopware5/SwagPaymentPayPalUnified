<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

abstract class PayPalApiStruct implements \JsonSerializable
{
    final public function __construct()
    {
    }

    /**
     * @return static
     */
    public function assign(array $arrayDataWithSnakeCaseKeys)
    {
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();

        foreach ($arrayDataWithSnakeCaseKeys as $snakeCaseKey => $value) {
            if ($value === [] || $value === null) {
                continue;
            }

            $camelCaseKey = \ucfirst($nameConverter->denormalize($snakeCaseKey));
            $setterMethod = \sprintf('set%s', $camelCaseKey);
            if (!\method_exists($this, $setterMethod)) {
                // There is no setter/property for a given data key from PayPal.
                // Continue here to not break the plugin, if the plugin is not up-to-date with the PayPal API
                continue;
            }

            if ($this->isScalar($value)) {
                $this->$setterMethod($value);

                continue;
            }

            $namespace = $this->getNamespaceOfAssociation();
            if ($this->isAssociativeArray($value)) {
                /** @var class-string<PayPalApiStruct> $className */
                $className = $namespace . $camelCaseKey;
                if (!\class_exists($className)) {
                    continue;
                }

                $instance = $this->createNewAssociation($className, $value);
                $this->$setterMethod($instance);

                continue;
            }

            // Value is not a list of objects
            if (!\is_array($value[0])) {
                $this->$setterMethod($value);

                continue;
            }

            /** @var class-string<PayPalApiStruct> $className */
            $className = $namespace . $this->getClassNameOfOneToManyAssociation($camelCaseKey);
            if (!\class_exists($className)) {
                continue;
            }

            $arrayWithToManyAssociations = [];
            foreach ($value as $toManyAssociation) {
                $instance = $this->createNewAssociation($className, $toManyAssociation);
                $arrayWithToManyAssociations[] = $instance;
            }
            $this->$setterMethod($arrayWithToManyAssociations);
        }

        return $this;
    }

    /**
     * @return mixed[]
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = [];
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();

        foreach (\get_object_vars($this) as $property => $value) {
            $snakeCasePropertyName = $nameConverter->normalize($property);

            $data[$snakeCasePropertyName] = $value;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $encoded = json_encode($this);
        if (!\is_string($encoded)) {
            throw new \RuntimeException('Could not encode PayPal data');
        }

        return json_decode($encoded, true);
    }

    /**
     * @param int|string|bool|array|PayPalApiStruct|null $value
     *
     * @return bool
     */
    private function isScalar($value)
    {
        return !\is_array($value);
    }

    /**
     * @return bool
     */
    private function isAssociativeArray(array $value)
    {
        return \array_keys($value) !== \range(0, \count($value) - 1);
    }

    /**
     * @return string
     */
    private function getNamespaceOfAssociation()
    {
        return \sprintf('%s\\', static::class);
    }

    /**
     * @param string $camelCaseKey
     *
     * @return string
     */
    private function getClassNameOfOneToManyAssociation($camelCaseKey)
    {
        return \rtrim($camelCaseKey, 's');
    }

    /**
     * @psalm-param class-string<PayPalApiStruct> $className
     *
     * @param string $className
     *
     * @return self
     */
    private function createNewAssociation($className, array $value)
    {
        $instance = new $className();
        $instance->assign($value);

        return $instance;
    }
}
