<?php

namespace FunnyDev\GoogleAnalytic;

use DateTime;
use FunnyDev\GoogleClient\GoogleServiceClient;
use Google\Client;
use Google\Service\Analytics;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\Cohort;
use Google\Service\AnalyticsData\CohortSpec;
use Google\Service\AnalyticsData\CohortsRange;
use Google\Service\AnalyticsData\RunReportRequest;

class GoogleAnalyticReportHelper
{
    public string $property_id;
    public int $daysCount;
    public string $startDate;
    public string $endDate;
    public DateRange $dateRange;
    public AnalyticsData $analytics;
    public Client $client;
    public Dimension $dimension;
    public Metric $metric;
    public Cohort $cohort;
    public CohortsRange $cohortRange;
    public CohortSpec $cohortSpec;

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function __construct(string $property_id='', string $metric='', string $dimension='', string $start_date='', string $end_date='', array $credentials=null, string $credentials_path=null)
    {
        if ($property_id !== '') {
            $this->property_id = $property_id;
        } else {
            $this->property_id = config('google-analytic.property_id');
        }
        $this->client = (new GoogleServiceClient($credentials, $credentials_path))->instance();
        $this->client->addScope(Analytics::ANALYTICS);
        $this->analytics = new AnalyticsData($this->client);
        if (!empty($dimension)) {
            $this->setDimension($dimension);
        }
        if (!empty($metric)) {
            $this->setMetric($metric);
        }
        if (!empty($start_date) && !empty($end_date)) {
            $this->startDate = $start_date;
            $this->endDate = $end_date;
            $this->daysCount = (new DateTime($start_date))->diff(new DateTime($end_date))->days+1;
            $this->setDateRange($start_date, $end_date);
        }
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function convert_days_to_date($days, $startDate): string
    {
        $date = new DateTime($startDate);
        $date->modify("+$days days");
        return $date->format('Y-m-d');
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function convert_date_keys($data, $startDate): array
    {
        $newData = [];
        foreach ($data as $daysSinceStart => $value) {
            $dayNumber = intval($daysSinceStart) - 1;
            $newData[$this->convert_days_to_date($dayNumber, $startDate)] = $value;
        }
        ksort($newData);
        return $newData;
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function add_date_values($data, $endDate): array
    {
        $newData = [];
        $currentData = array_values($data);
        $today = new DateTime($endDate);
        foreach ($currentData as $value) {
            $dateKey = $today->format('Y-m-d');
            $newData[$dateKey] = $value;
            $today->modify('-1 day');
        }
        ksort($newData);
        return $newData;
    }

    public function calculate_average_values($data): float|int
    {
        if (empty($data)) {
            return 0;
        }
        $sum = 0;
        foreach ($data as $value) {
            $sum += $value;
        }

        return $sum / count($data);
    }

    public function calculate_sum_values($data): float|int
    {
        if (empty($data)) {
            return 0;
        }
        $sum = 0;
        foreach ($data as $value) {
            $sum += $value;
        }

        return $sum;
    }

    public function remove_query_keys($data, $format='int'): array
    {
        $newData = [];
        foreach ($data as $key => $value) {
            $key = explode('?', $key)[0];
            if (isset($newData[$key])) {
                if ($format == 'int') {
                    $newData[$key] += intval($value);
                } elseif ($format == 'float') {
                    $newData[$key] += floatval($value);
                } else {
                    $newData[$key] .= ' '.$value;
                }
            } else {
                if ($format == 'int') {
                    $newData[$key] = intval($value);
                } elseif ($format == 'float') {
                    $newData[$key] = floatval($value);
                } else {
                    $newData[$key] = $value;
                }
            }
        }
        arsort($newData);
        return $newData;
    }

    public function convert_google_result($data): array
    {
        $results = [];
        foreach ($data->getRows() as $row) {
            $key = $row->getDimensionValues()[0]->getValue();
            $value = $row->getMetricValues()[0]->getValue();
            if (($key == '(not set)') || ($key == '')) {
                $key = 'unknown';
            }
            if (isset($results[$key])) {
                if (is_string($results[$key])) {
                    $results[$key] .= ' '.$value;
                } else {
                    $results[$key] += $value;
                }
            } else {
                $results[$key] = $value;
            }
        }

        return $results;
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function setDateRange(string $start_date='yesterday', string $end_date='today'): DateRange
    {
        $dateRange = new DateRange();
        $dateRange->setStartDate($start_date);
        $dateRange->setEndDate($end_date);
        $this->dateRange = $dateRange;
        return $dateRange;
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function setMetric(string $metric): Metric
    {
        $instance = new Metric();
        $instance->setName($metric);
        $this->metric = $instance;
        return $instance;
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function setDimension(string $dimension): Dimension
    {
        $instance = new Dimension();
        $instance->setName($dimension);
        $this->dimension = $instance;
        return $instance;
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function setCohort(): Cohort
    {
        $instance = new Cohort();
        $instance->setName('Cohort 1');
        $instance->setDimension('firstSessionDate');
        $instance->setDateRange($this->dateRange);
        $this->cohort = $instance;
        return $instance;
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function setCohortRange(): CohortsRange
    {
        $instance = new CohortsRange();
        $instance->setGranularity('DAILY');
        $instance->setStartOffset(0);
        $instance->setEndOffset($this->daysCount);
        $this->cohortRange = $instance;
        return $instance;
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function setCohortSpec(): CohortSpec
    {
        $instance = new CohortSpec();
        $instance->setCohorts([$this->cohort]);
        $instance->setCohortsRange($this->cohortRange);
        $this->cohortSpec = $instance;
        return $instance;
    }

    private function applySorting($result, $sortBy, $orderBy): array
    {
        switch ($sortBy) {
            case 'key':
                $orderBy === 'desc' ? krsort($result) : ksort($result);
                break;
            case 'value':
                $orderBy === 'desc' ? arsort($result) : asort($result);
                break;
        }

        return $result;
    }

    /**
     * Retrieve and sort Google Analytic report data
     *
     * @param string $sortBy An optional parameter that determines the sorting of the report data. Can be "", "key", or "value". When "", no sorting is applied.
     * @param string $orderBy An optional parameter that determines the order direction of the sorting. Can be "asc" or "desc". Default is "desc".
     *
     * @return array|bool Returns report data as an array if successful, or false if unsuccessful.
     * @throws \Exception If $sortBy is neither "", "key", or "value".
     * @throws \Google\Service\Exception
     *
     */
    public function getReport(string $sortBy = '', string $orderBy = 'desc'): array|bool
    {
        if (!in_array($sortBy, ['', 'key', 'value'])) {
            throw new \Exception("Invalid sortBy value. It should be either '', 'key', or 'value'");
        }

        $request = new RunReportRequest();
        $request->setDateRanges([$this->dateRange]);
        $request->setDimensions([$this->dimension]);
        $request->setMetrics([$this->metric]);
        $result = $this->analytics->properties->runReport('properties/' . $this->property_id, $request);

        if ($result) {
            return $this->applySorting($this->convert_google_result($result), $sortBy, $orderBy);
        }

        return false;
    }

    /**
     * @throws \Exception
     * @throws \Google\Service\Exception
     */
    public function getCohortReport(): array|bool
    {
        $request = new RunReportRequest();
        $this->setCohort();
        $this->setCohortRange();
        $this->setCohortSpec();
        $cohort_dimension = new Dimension();
        $cohort_dimension->setName('cohort');
        $request->setDimensions([$cohort_dimension, $this->dimension]);
        $request->setMetrics([$this->metric]);
        $request->setCohortSpec($this->cohortSpec);
        $result = $this->analytics->properties->runReport('properties/'.$this->property_id, $request);

        if ($result) {
            return $this->convert_google_result($result);
        }

        return false;
    }
}
