<?php

namespace FunnyDev\GoogleAnalytic\Tests;

use FunnyDev\GoogleAnalytic\GoogleAnalyticReportHelper;

class GoogleAnalyticReportHelperPureTest extends TestCase
{
    private function helperWithoutConstructor(): GoogleAnalyticReportHelper
    {
        return (new \ReflectionClass(GoogleAnalyticReportHelper::class))->newInstanceWithoutConstructor();
    }

    public function test_convert_days_to_date(): void
    {
        $helper = $this->helperWithoutConstructor();

        $this->assertSame('2025-01-02', $helper->convert_days_to_date(1, '2025-01-01'));
        $this->assertSame('2025-01-01', $helper->convert_days_to_date(0, '2025-01-01'));
    }

    public function test_convert_date_keys_sorts_and_converts_from_days_since_start(): void
    {
        $helper = $this->helperWithoutConstructor();

        $input = [
            '3' => 30,
            '1' => 10,
            '2' => 20,
        ];
        $out = $helper->convert_date_keys($input, '2025-01-01');

        $this->assertSame([
            '2025-01-01' => 10,
            '2025-01-02' => 20,
            '2025-01-03' => 30,
        ], $out);
    }

    public function test_add_date_values_assigns_values_from_end_date_backwards(): void
    {
        $helper = $this->helperWithoutConstructor();

        $out = $helper->add_date_values(['a' => 1, 'b' => 2], '2025-01-02');

        $this->assertSame([
            '2025-01-01' => 2,
            '2025-01-02' => 1,
        ], $out);
    }

    public function test_calculate_average_values_and_sum_values(): void
    {
        $helper = $this->helperWithoutConstructor();

        $this->assertSame(0, $helper->calculate_average_values([]));
        $this->assertSame(0, $helper->calculate_sum_values([]));

        $this->assertEquals(2.0, $helper->calculate_average_values([1, 2, 3]));
        $this->assertSame(6, $helper->calculate_sum_values([1, 2, 3]));
    }

    public function test_remove_query_keys_merges_and_sums_int_values_by_default(): void
    {
        $helper = $this->helperWithoutConstructor();

        $out = $helper->remove_query_keys([
            '/a?x=1' => '2',
            '/a?y=2' => '3',
            '/b' => 1,
        ]);

        // arsort() sorts by value descending
        $this->assertSame([
            '/a' => 5,
            '/b' => 1,
        ], $out);
    }

    public function test_convert_google_result_replaces_not_set_with_unknown_and_concatenates_duplicates(): void
    {
        $helper = $this->helperWithoutConstructor();

        $data = new class {
            public function getRows(): array
            {
                return [
                    new class {
                        public function getDimensionValues(): array
                        {
                            return [new class { public function getValue() { return '(not set)'; } }];
                        }

                        public function getMetricValues(): array
                        {
                            return [new class { public function getValue() { return '1'; } }];
                        }
                    },
                    new class {
                        public function getDimensionValues(): array
                        {
                            return [new class { public function getValue() { return '(not set)'; } }];
                        }

                        public function getMetricValues(): array
                        {
                            return [new class { public function getValue() { return '2'; } }];
                        }
                    },
                ];
            }
        };

        $out = $helper->convert_google_result($data);
        $this->assertSame([
            'unknown' => '1 2',
        ], $out);
    }
}
