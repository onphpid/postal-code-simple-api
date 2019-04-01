<?php
declare(strict_types=1);

namespace OnPhpId\IndonesiaPostalCode;

use Pentagonal\DatabaseDBAL\Database;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

/**
 * @var \Slim\App $this
 */
// http://example.com/provinces
$this->get(
    '/provinces[/]',
    function (ServerRequestInterface $request, ResponseInterface $response) {
        /**
         * @var Database[] $this
         * @var Response $response
         */
        $db = $this['db'];
        $stmt = $db
            ->createQueryBuilder()
            ->select('*')
            ->from('db_province_data')
            ->execute();
        $data = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = [
                'code' => $row['province_code'],
                'name' => [
                    'id' => $row['province_name'],
                    'en' => $row['province_name_en']
                ],
            ];
        }
        $stmt->closeCursor();
        return $response->withJson(['data' => $data], 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    });

// SHOW BY POSTAL CODE
// http://example.com/postal/{postal_code: [0-9]{5}}
$this->get(
    '/postal/{code: [0-9]+}[/]',
    function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
        /**
         * @var Database[] $this
         * @var Response $response
         */
        if (strlen($params['code']) <> 5) {
            return $response->withJson(
                ['message' => sprintf('Postal code %s is not valid', $params['code'])],
                412
            );
        }
        $db = $this['db'];
        $stmt = $db
            ->createQueryBuilder()
            ->select('
                db_postal_code_data.id as postal_id,
                db_province_data.id as province_id, 
                db_postal_code_data.postal_code, 
                db_postal_code_data.province_code, 
                db_postal_code_data.urban, 
                db_postal_code_data.sub_district, 
                db_postal_code_data.city,
                db_province_data.province_name, 
                db_province_data.province_name_en
            ')
            ->from('db_postal_code_data')
            ->innerJoin(
                'db_postal_code_data',
                'db_province_data',
                '',
                'db_province_data.province_code = db_postal_code_data.province_code'
            )
            ->where('db_postal_code_data.postal_code = :postalCode')
            ->setParameter(':postalCode', $params['code'])
            ->execute();
        if (!$stmt) {
            return $response->withJson(
                ['message' => 'There was an error while executing query'],
                417
            );
        }

        $data = [
            'province' => [],
            'postal' => []
        ];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data['province']['code'] = $row['province_code'];
            $data['province']['name'] = [
                'id' => $row['province_name'],
                'en' => $row['province_name_en']
            ];
            $data['postal'][] = [
                'code' => $row['postal_code'],
                'urban' => $row['urban'],
                'sub_district' => $row['sub_district'],
                'city' => $row['city'],
            ];
        }

        $stmt->closeCursor();
        // expectation failed of not found
        if (empty($data['province'])) {
            return $response->withJson(
                ['message' => sprintf('Postal code %s has not found', $params['code'])],
                417
            );
        }

        return $response->withJson(['data' => $data], 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    });

// SHOW BY SUB URBAN & UP
// http://example.com/urban/{urban}
// http://example.com/urban/{urban}/{sub_district}
// http://example.com/urban/{urban}/{sub_district}/{city}
// http://example.com/urban/{urban}/{sub_district}/{city}/{province:code|name}
$this->get(
    '/urban/{urban: [^\/]+}[/[{sub_district: [^\/]+}[/[{city: [^\/]+}[/[{province: [^\/]+}[/]]]]]]]',
    function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
        $urban = trim($params['urban']);
        $sub_district = isset($params['sub_district'])
            ? trim($params['sub_district'])
            : '';
        $city = isset($params['city'])
            ? trim($params['city'])
            : '';
        $province = isset($params['province'])
            ? trim($params['province'])
            : '';
        $isProvinceNumeric = is_numeric($province) && strlen($province) === 2;
        /**
         * @var Database[] $this
         * @var Response $response
         */
        if (strlen($urban) === '' || preg_match('/[^a-z \.0-9\-\'\"]/i', $urban)) {
            return $response->withJson(
                ['message' => sprintf('Urban name %s is not valid', $urban)],
                412
            );
        }

        $db = $this['db'];
        $stmt = $db
            ->createQueryBuilder()
            ->select('
                db_postal_code_data.id as postal_id,
                db_province_data.id as province_id, 
                db_postal_code_data.postal_code, 
                db_postal_code_data.province_code, 
                db_postal_code_data.urban, 
                db_postal_code_data.sub_district, 
                db_postal_code_data.city,
                db_province_data.province_name, 
                db_province_data.province_name_en
            ')
            ->from('db_postal_code_data')
            ->innerJoin(
                'db_postal_code_data',
                'db_province_data',
                '',
                'db_province_data.province_code = db_postal_code_data.province_code'
            )
            ->where('LOWER(db_postal_code_data.urban) = LOWER(:urbanName)')
            ->setParameter(':urbanName', $urban);
        if ($sub_district !== '') {
            $stmt = $stmt
                ->andWhere('LOWER(db_postal_code_data.sub_district) = LOWER(:subDistrict)')
                ->setParameter(':subDistrict', $sub_district);
        }

        if ($city !== '') {
            $stmt = $stmt
                ->andWhere('LOWER(db_postal_code_data.city) = LOWER(:city)')
                ->setParameter(':city', $city);
        }
        if ($province !== '') {
            $stmt = $stmt
                ->andWhere(
                    $isProvinceNumeric
                        ? 'db_province_data.province_code = :province'
                        : 'LOWER(db_province_data.province_name) = LOWER(:province)'
                )
                ->setParameter(':province', $province);
        }

        $stmt = $stmt->execute();
        if (!$stmt) {
            return $response->withJson(
                ['message' => 'There was an error while executing query'],
                417
            );
        }

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $var = [];
            $code = $row['province_code'];
            if (!isset($data[$code])) {
                $data[$code] = [];
            }

            if (!isset($data[$code]['postal'])) {
                $data[$code]['province'] = [];
                $data[$code]['postal'] = [];
            }
            $data[$code]['province']['code'] = $row['province_code'];
            $data[$code]['province']['name'] = [
                'id' => $row['province_name'],
                'en' => $row['province_name_en']
            ];
            $data[$code]['postal'][] = [
                'code' => $row['postal_code'],
                'urban'  => $row['urban'],
                'sub_district' => $row['sub_district'],
                'city' => $row['city'],
            ];
        }

        $stmt->closeCursor();
        // expectation failed of not found
        if (empty($data)) {
            return $response->withJson(
                ['message' => sprintf('Urban %s has not found', $urban)],
                417
            );
        }

        return $response->withJson(['data' => array_values($data)], 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    });

// SHOW BY SUB DISTRICT & SUB
// http://example.com/sub_district/{sub_district}
// http://example.com/sub_district/{sub_district}/{city}
// http://example.com/sub_district/{sub_district}/{city}/{province:code|name}
$this->get(
    '/sub_district/{sub_district: [^\/]+}[/[{city: [^\/]+}[/[{province: [^\/]+}[/]]]]]',
    function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
        $sub_district = isset($params['sub_district'])
            ? trim($params['sub_district'])
            : '';
        $city = isset($params['city'])
            ? trim($params['city'])
            : '';
        $province = isset($params['province'])
            ? trim($params['province'])
            : '';
        $isProvinceNumeric = is_numeric($province) && strlen($province) === 2;
        /**
         * @var Database[] $this
         * @var Response $response
         */
        if (strlen($sub_district) === '' || preg_match('/[^a-z \.0-9\-\'\"]/i', $sub_district)) {
            return $response->withJson(
                ['message' => sprintf('Sub district name %s is not valid', $sub_district)],
                412
            );
        }

        $db = $this['db'];
        $stmt = $db
            ->createQueryBuilder()
            ->select('
                db_postal_code_data.id as postal_id,
                db_province_data.id as province_id, 
                db_postal_code_data.postal_code, 
                db_postal_code_data.province_code, 
                db_postal_code_data.urban, 
                db_postal_code_data.sub_district, 
                db_postal_code_data.city,
                db_province_data.province_name, 
                db_province_data.province_name_en
            ')
            ->from('db_postal_code_data')
            ->innerJoin(
                'db_postal_code_data',
                'db_province_data',
                '',
                'db_province_data.province_code = db_postal_code_data.province_code'
            )
            ->where('LOWER(db_postal_code_data.sub_district) = LOWER(:subDistrict)')
            ->setParameter(':subDistrict', $sub_district);
        if ($city !== '') {
            $stmt = $stmt
                ->andWhere('LOWER(db_postal_code_data.city) = LOWER(:city)')
                ->setParameter(':city', $city);
        }

        if ($province !== '') {
            $stmt = $stmt
                ->andWhere(
                    $isProvinceNumeric
                        ? 'db_province_data.province_code = :province'
                        : 'LOWER(db_province_data.province_name) = LOWER(:province)'
                )
                ->setParameter(':province', $province);
        }

        $stmt = $stmt->execute();
        if (!$stmt) {
            return $response->withJson(
                ['message' => 'There was an error while executing query'],
                417
            );
        }

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $var = [];
            $code = $row['province_code'];
            if (!isset($data[$code])) {
                $data[$code] = [];
            }

            if (!isset($data[$code]['postal'])) {
                $data[$code]['province'] = [];
                $data[$code]['postal'] = [];
            }

            $data[$code]['province']['code'] = $row['province_code'];
            $data[$code]['province']['name'] = [
                'id' => $row['province_name'],
                'en' => $row['province_name_en']
            ];
            $data[$code]['postal'][] = [
                'code' => $row['postal_code'],
                'urban'  => $row['urban'],
                'sub_district' => $row['sub_district'],
                'city' => $row['city'],
            ];
        }

        $stmt->closeCursor();
        // expectation failed of not found
        if (empty($data)) {
            return $response->withJson(
                ['message' => sprintf('Sub district %s has not found', $sub_district)],
                417
            );
        }

        return $response->withJson(['data' => array_values($data)], 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    });

// SHOW BY CITY & SUB
// http://example.com/city/{city}
// http://example.com/city/{sub_district}
// http://example.com/city/{sub_district}/{urban}
$this->get(
    '/city/{city: [^\/]+}[/[{sub_district: [^\/]+}[/[{urban: [^\/]+}[/]]]]]',
    function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
        $city = isset($params['city'])
            ? trim($params['city'])
            : '';
        $sub_district = isset($params['sub_district'])
            ? trim($params['sub_district'])
            : '';
        $urban = isset($params['urban'])
            ? trim($params['urban'])
            : '';
        /**
         * @var Database[] $this
         * @var Response $response
         */
        if (strlen($city) === '' || preg_match('/[^a-z \.0-9\-\'\"]/i', $city)) {
            return $response->withJson(
                ['message' => sprintf('City name %s is not valid', $city)],
                412
            );
        }

        $db = $this['db'];
        $stmt = $db
            ->createQueryBuilder()
            ->select('
                db_postal_code_data.id as postal_id,
                db_province_data.id as province_id, 
                db_postal_code_data.postal_code, 
                db_postal_code_data.province_code, 
                db_postal_code_data.urban, 
                db_postal_code_data.sub_district, 
                db_postal_code_data.city,
                db_province_data.province_name, 
                db_province_data.province_name_en
            ')
            ->from('db_postal_code_data')
            ->innerJoin(
                'db_postal_code_data',
                'db_province_data',
                '',
                'db_province_data.province_code = db_postal_code_data.province_code'
            )
            ->where('LOWER(db_postal_code_data.city) = LOWER(:city)')
            ->setParameter(':city', $city);
        if ($sub_district !== '') {
            $stmt = $stmt
                ->andWhere('LOWER(db_postal_code_data.sub_district) = LOWER(:subDistrict)')
                ->setParameter(':subDistrict', $sub_district);
        }
        if ($urban !== '') {
            $stmt = $stmt
                ->andWhere('LOWER(db_postal_code_data.urban) = LOWER(:urban)')
                ->setParameter(':urban', $urban);
        }

        $stmt = $stmt->execute();
        if (!$stmt) {
            return $response->withJson(
                ['message' => 'There was an error while executing query'],
                417
            );
        }

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $var = [];
            $code = $row['province_code'];
            if (!isset($data[$code])) {
                $data[$code] = [];
            }

            if (!isset($data[$code]['postal'])) {
                $data[$code]['province'] = [];
                $data[$code]['postal'] = [];
            }
            $data[$code]['province']['code'] = $row['province_code'];
            $data[$code]['province']['name'] = [
                'id' => $row['province_name'],
                'en' => $row['province_name_en']
            ];
            $data[$code]['postal'][] = [
                'code' => $row['postal_code'],
                'urban'  => $row['urban'],
                'sub_district' => $row['sub_district'],
                'city' => $row['city'],
            ];
        }

        $stmt->closeCursor();
        // expectation failed of not found
        if (empty($data)) {
            return $response->withJson(
                ['message' => sprintf('City %s has not found', $city)],
                417
            );
        }

        return $response->withJson(['data' => array_values($data)], 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    });

// SHOW BY PROVINCE & SUB
// http://example.com/province/{province:code|name}
// http://example.com/province/{province}/{city}
// http://example.com/province/{province}/{city}/{sub_district}
// http://example.com/province/{province}/{city}/{sub_district}/{urban}
$this->get(
    '/province/{province: [^\/]+}[/[{city: [^\/]+}[/[{sub_district: [^\/]+}[/[{urban: [^\/]+}[/]]]]]]]',
    function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
        $province = isset($params['province'])
            ? trim($params['province'])
            : '';
        $isProvinceNumeric = is_numeric($province) && strlen($province) === 2;
        $city = isset($params['city'])
            ? trim($params['city'])
            : '';
        $sub_district = isset($params['sub_district'])
            ? trim($params['sub_district'])
            : '';
        $urban = isset($params['urban'])
            ? trim($params['urban'])
            : '';
        /**
         * @var Database[] $this
         * @var Response $response
         */
        if (strlen($province) === '' || preg_match('/[^a-z \.0-9\-\'\"]/i', $province)) {
            return $response->withJson(
                ['message' => sprintf('Province name %s is not valid', $province)],
                412
            );
        }

        $db = $this['db'];
        $stmt = $db
            ->createQueryBuilder()
            ->select('
                db_postal_code_data.id as postal_id,
                db_province_data.id as province_id, 
                db_postal_code_data.postal_code, 
                db_postal_code_data.province_code, 
                db_postal_code_data.urban, 
                db_postal_code_data.sub_district, 
                db_postal_code_data.city,
                db_province_data.province_name, 
                db_province_data.province_name_en
            ')
            ->from('db_postal_code_data')
            ->innerJoin(
                'db_postal_code_data',
                'db_province_data',
                '',
                'db_province_data.province_code = db_postal_code_data.province_code'
            )
            ->where($isProvinceNumeric
                ? 'LOWER(db_province_data.province_code) = LOWER(:province)'
                : 'LOWER(db_province_data.province_name) = LOWER(:province)'
            )
            ->setParameter(':province', $province);

        if ($city !== '') {
            $stmt = $stmt
                ->andWhere('LOWER(db_postal_code_data.city) = LOWER(:city)')
                ->setParameter(':city', $city);
        }
        if ($sub_district !== '') {
            $stmt = $stmt
                ->andWhere('LOWER(db_postal_code_data.sub_district) = LOWER(:subDistrict)')
                ->setParameter(':subDistrict', $sub_district);
        }
        if ($urban !== '') {
            $stmt = $stmt
                ->andWhere('LOWER(db_postal_code_data.urban) = LOWER(:urban)')
                ->setParameter(':urban', $urban);
        }

        $stmt = $stmt->execute();
        if (!$stmt) {
            return $response->withJson(
                ['message' => 'There was an error while executing query'],
                417
            );
        }

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $var = [];
            $code = $row['province_code'];
            if (!isset($data[$code])) {
                $data[$code] = [];
            }

            if (!isset($data[$code]['postal'])) {
                $data[$code]['province'] = [];
                $data[$code]['postal'] = [];
            }
            $data[$code]['province']['code'] = $row['province_code'];
            $data[$code]['province']['name'] = [
                'id' => $row['province_name'],
                'en' => $row['province_name_en']
            ];
            $data[$code]['postal'][] = [
                'code' => $row['postal_code'],
                'urban'  => $row['urban'],
                'sub_district' => $row['sub_district'],
                'city' => $row['city'],
            ];
        }

        $stmt->closeCursor();
        // expectation failed of not found
        if (empty($data)) {
            return $response->withJson(
                ['message' => sprintf('City %s has not found', $city)],
                417
            );
        }

        return $response->withJson(['data' => array_values($data)], 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    });

// SHOW ENDPOINT
// http://example.com/
$this->get(
    '[/]',
    function (ServerRequestInterface $request, ResponseInterface $response) {
        /**
         * @var Response $response
         */
        return $response->withJson([
            'data' => [
                'endpoint' => [
                    '/provinces' => 'Get list provinces',
                    '/postal' => 'Get list by postal code',
                    '/urban' => 'Get list by urban',
                    '/sub_district' => 'Get list by sub district',
                    '/city' => 'Get list by city',
                    '/province' => 'Get list by province',
                ]
            ]
        ], 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    });
