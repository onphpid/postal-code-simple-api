# ROUTE LIST FOR SIMPLE API

This api contains some routes as simple url access.


## RESPONSE HEADER

```text
Content-Type: application/json
```

## HTTP HEADER STATUS CODE

> 200 success

```json
{
    "data": "...any object or data"
}
``` 
> 412 precondition failed

The parameter or logic is not valid sent by client

```json
{
    "message" : "message error about precondition failed"
}
```

> 417 expectation failed

The result is not found or can not serve to client maybe caused no data found in database 

```json
{
    "message" : "message error about expectation failed"
}
```

And any common http code

## ROUTES LIST

### Path: /

Main path is info endpoint lists

> endpoint: 

`/`

> example response:

```text
http://example.com/
```

```json
{
    "data": {
        "endpoint": {
            "/provinces": "Get list provinces",
            "/postal": "Get list by postal code",
            "/urban": "Get list by urban",
            "/sub_district": "Get list by sub district",
            "/city": "Get list by city",
            "/province": "Get list by province"
        }
    }
}
```

### Path: /provinces

Path is info of lists provinces

> endpoint: 

`/provinces`

> example response:

```json
{
    "data": [
        {
            "code": "11",
            "name": {
                "id": "ACEH",
                "en": "ACEH"
            }
        },
        {
            "code": "12",
            "name": {
                "id": "SUMATERA UTARA",
                "en": "NORTH SUMATERA"
            }
        },
        {"...": "...any like above"}
    ]
}
```

### Path: /postal

Path is info of lists provinces & area by postal code

> endpoint: 

`/postal/(integer:postal_code(5) eg: 11011)`

> example response:

```text
http://example.com/postal/13310
```

Data is as array object mapping

```json
{
    "data": {
        "province": {
            "code": "31",
            "name": {
                "id": "DAERAH KHUSUS IBUKOTA JAKARTA",
                "en": "SPECIAL CAPITAL REGION OF JAKARTA"
            }
        },
        "postal": [
            {
                "code": "13310",
                "urban": "BALI MESTER",
                "sub_district": "JATINEGARA",
                "city": "JAKARTA TIMUR"
            }
        ]
    }
}
```

### Path: /urban

Path is info of lists provinces

> endpoint: 

`/urban/(string:urbanName)`
`/urban/(string:urbanName)/(string:subDistrictName)`
`/urban/(string:urbanName)/(string:subDistrictName)/(string:cityName)`
`/urban/(string:urbanName)/(string:subDistrictName)/(string:cityName)/(string:provinceName)`

> example response:

```text
http://example.com/urban/BALI%20MESTER
```

Data is as array list mapping

```json
{
    "data": [
        {
            "province": {
                "code": "31",
                "name": {
                    "id": "DAERAH KHUSUS IBUKOTA JAKARTA",
                    "en": "SPECIAL CAPITAL REGION OF JAKARTA"
                }
            },
            "postal": [
                {
                    "code": "13310",
                    "urban": "BALI MESTER",
                    "sub_district": "JATINEGARA",
                    "city": "JAKARTA TIMUR"
                }
            ]
        }
    ]
}
```

### Path: /sub_district

Path is info of lists provinces & area by sub district & sub value

> endpoint: 

`/sub_district/(string:subDistrictName)`
`/sub_district/(string:subDistrictName)/(string:cityName)`

> example response:

```text
http://example.com/sub_district/jatinegara
```

Data is as array list mapping

```json
{
    "data": [
        {
            "province": {
                "code": "31",
                "name": {
                    "id": "DAERAH KHUSUS IBUKOTA JAKARTA",
                    "en": "SPECIAL CAPITAL REGION OF JAKARTA"
                }
            },
            "postal": [
                {
                    "code": "13310",
                    "urban": "BALI MESTER",
                    "sub_district": "JATINEGARA",
                    "city": "JAKARTA TIMUR"
                },
                {
                    "code": "13330",
                    "urban": "BIDARACINA",
                    "sub_district": "JATINEGARA",
                    "city": "JAKARTA TIMUR"
                },
                {"...": "...any like above"}
            ]
        }
    ]
}
```


### Path: /city

Path is info of lists provinces & area by city & sub value

> endpoint: 

`/city/(string:cityName)`
`/city/(string:cityName)/(string:subDistrictName)`
`/city/(string:cityName)/(string:subDistrictName)/(string:urbanName)`

> example response:

```text
http://example.com/city/jakarta%20timur
```

Data is as array list mapping

```json
{
    "data": [
        {
            "province": {
                "code": "31",
                "name": {
                    "id": "DAERAH KHUSUS IBUKOTA JAKARTA",
                    "en": "SPECIAL CAPITAL REGION OF JAKARTA"
                }
            },
            "postal": [
                {
                    "code": "13530",
                    "urban": "BALEKAMBANG",
                    "sub_district": "KRAMAT JATI",
                    "city": "JAKARTA TIMUR"
                },
                {
                    "code": "13310",
                    "urban": "BALI MESTER",
                    "sub_district": "JATINEGARA",
                    "city": "JAKARTA TIMUR"
                },
                {"...": "...any like above"}
            ]
        }
    ]
}
```

### Path: /province

Path is info of lists provinces & area by city & sub value

> endpoint: 

`/province/(string:provinceName)`
`/province/(string:provinceName)/(string:cityName)`
`/province/(string:provinceName)/(string:cityName)/(string:subDistrictName)`
`/province/(string:provinceName)/(string:cityName)/(string:subDistrictName)/(string:urbanName)`

> example response:

```text
http://example.com/province/daerah%20khusus%20ibukota%20jakarta
```

Data is as array list mapping

```json
{
    "data": [
        {
            "province": {
                "code": "31",
                "name": {
                    "id": "DAERAH KHUSUS IBUKOTA JAKARTA",
                    "en": "SPECIAL CAPITAL REGION OF JAKARTA"
                }
            },
            "postal": [
                {
                    "code": "14430",
                    "urban": "ANCOL",
                    "sub_district": "PADEMANGAN",
                    "city": "JAKARTA UTARA"
                },
                {
                    "code": "11330",
                    "urban": "ANGKE",
                    "sub_district": "TAMBORA",
                    "city": "JAKARTA BARAT"
                },
                {"...": "...any like above"}
            ]
        }
    ]
}
```
