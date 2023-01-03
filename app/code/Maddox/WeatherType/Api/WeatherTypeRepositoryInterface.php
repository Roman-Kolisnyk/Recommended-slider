<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Api;

interface WeatherTypeRepositoryInterface
{
    /**
     * @param \Maddox\WeatherType\Api\WeatherTypeInterface $weatherType
     * @return WeatherTypeInterface
     */
    public function save(WeatherTypeInterface $weatherType): WeatherTypeInterface;

    /**
     * @param \Maddox\WeatherType\Api\WeatherTypeInterface $weatherType
     * @return void
     */
    public function delete(WeatherTypeInterface $weatherType): void;

    /**
     * @param int|string $weatherTypeId
     * @return void
     */
    public function deleteById(int|string $weatherTypeId): void;

    /**
     * @param int|string $weatherTypeId
     * @return \Maddox\WeatherType\Api\WeatherTypeInterface
     */
    public function get(int|string $weatherTypeId): \Maddox\WeatherType\Api\WeatherTypeInterface;
}
