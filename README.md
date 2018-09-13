# Laravel Query Builder
This is query builder which is acting as a helper for querying.

# Using of QUery Builder:
1. Create a folder in side your app folder and store the query builder there.
2. Import it in your controller using use App\QueryHelper\QueryHelper; or whatever name you provide.
3. In the constructor function initialize it by giving your corresponding model as paramer.It will create an instance of the given model.
```php
public function __construct()
{
     $this->helper = new QueryHelper(Product::class);
}
```

4. Now call the functions anywhere in the controller function.Example:
```php
public function getByQuery(Request $request)
{
        $joiningClause = $request->input('joiningClause') ? $request->input('joiningClause') : null;
        $whereClause = $request->has('whereClause') ? $request->input('whereClause') : null;
        $orderBy = $request->input('orderBy') ? $request->input('orderBy') : null;
        $fields = $request->input('selectedFields');
        $products = $this->helper->getResultByComplex($whereClause, $joiningClause, $fields, $orderBy);
        return response($products, 200);
}
```

5. For relational query please add relational functions in the model.
 
 Example:
 
 ```php
 class Product extends Model
 { 
     /**
      * The attributes that are mass assignable.
      *
      * @var array
      */
     protected $fillable = ['product_title','product_details','product_price','product_stock'];
 
     public function productdenormalizes(){
         return $this->hasMany(Productdenormalize::class);
     }
     public function metas(){
         return $this->hasMany(Meta::class);
     }
 }
 ```


## Functions includes.

### GetAllData($relationFncs = null)
Params:
1. Array of relational tables name:
example:['tags','comments'].

Response:
Collection of all data coming from data base.

### GetByPagination($resultPerPage, $relationFncs = null)

Params:
1. Result per page
2. Array of relational tables name:
example:['tags','comments'].

Response:
Collection of all data coming from data base.

### GetResultByComplex($parentCondition = null, $joiningcondition = null, $fields = null, $orderby = null, $pageNumber = null, $itemPerPage = null)

Params:
1. ParentCondition: example.
```php
$whereClause = [
    "and" => [
        "eql" => [
            "metas.meta_name" => "Et."
        ],
        "eql" => [
            "metas.meta_value" => "Fugit."
        ],
        "eql" => [
            "products.product_stock" => "289"
        ]
    ],
    "or" => [
        "eql"=>[
            "product_title" => "Dolores mollitia."
        ],
        "gt"=>[
            "product_price" => "47"
        ]
    ]
];
```
2. joiningClause. Example:
```php
$condition = [
    'metas' => [
        "eql" => [
            "metas.product_id",
            "products.id"
        ]
    ],
    "productdenormalizes" => [
        "eql" => [
            "productdenormalizes.product_id",
            "products.id"
        ]
    ]
];
```
Here 'metas' and 'productdenormalizes' are the table with which 
the main table is trying to join and get filtered data.

3.Fields: Example:
```php
$fields = 'products.*,productdenormalizes.product_tag,metas.meta_name,metas.meta_value';
```
Here 'metas' and 'productdenormalizes' are the table with which 
the main table is trying to join and get filtered data.

4. OrderBy: Example:
```php
$orderBy = [
    "product_price" => "asc"
];
```

5. PageNumber: Example:
```php
$itemPerPage = 10;
```

6. ItemPerPage: Example:
```php
$pageNumber = 2;
        
```

Exaple of calling the Function:
```php
$this->helper->getResultByComplex($whereClause, $condition, $fields, $orderBy, $pageNumber, $itemPerPage);     
```

Response:
Collection of all data coming from data base.

### Insert($tableName,$data)
Inserting data to table

Params:
1. tablename
2. data to be inserted

Example:

```php
public function store(Request $request)
{
   $allData = $request->input('data');
   $table = $request->input('table');
   try {
        $insertResult = $this->helper->insert($table, $allData);
        return response($insertResult, 200);
    } catch (BadRequestHttpException $e) {
        return response($e, 500);
    }
}    
```

Response:
Collection of all data coming from data base.

### GetDataBySqlFilter($whereClause = [], $orderBy = [], $pageNumber = null, $itemPerPage = null, $relationalFncs = null)

Params:
1. whereClause: example.
```php
$whereClause = [
    "and" => [
        "eql" => [
            "metas.meta_name" => "Et."
        ],
        "eql" => [
            "metas.meta_value" => "Fugit."
        ],
        "eql" => [
            "products.product_stock" => "289"
        ]
    ],
    "or" => [
        "eql"=>[
            "product_title" => "Dolores mollitia."
        ],
        "gt"=>[
            "product_price" => "47"
        ]
    ]
];
```

2. OrderBy: Example:
```php
$orderBy = [
   "product_price" => "asc"
];
```

3. PageNumber: Example:
```php
$itemPerPage = 10;
```

4. ItemPerPage: Example:
```php
$pageNumber = 2; 
```
5. Array of relational tables name:
example:['tags','comments'].

Exaple of calling the Function:
```php
$products = $this->helper->getDataBySqlFilter($whereClause, $orderBy, $pageNumber, $itemPerPage, $relationalFncs);     
```

Response:
Collection of all data coming from data base.

# Postman Query

## Functions Includes:

### getResultByComplex()
```json
   {
    "whereClause":{
        "and":{
            "lt":{
                "product_price":20
            }
        }	
    },
    "joiningClause":{
        "metas":{
            "eql":[
                "metas.product_id",
                   "products.id"
            ]
        },
        "productdenormalizes":{
            "eql":[
                "productdenormalizes.product_id",
                   "products.id"
            ]
        }
    },
    "selectedFields":"products.product_title as ProductTitle,products.product_price as ProductPrice,productdenormalizes.product_tag,metas.meta_name,metas.meta_value",
    "orderBy":{
        "product_price":"desc"
    }
}
```

### insert()

```json
{
    "table":"products",
    "data":{
        "product_title": "Hello world",
        "product_details": "Voluptates et libero enim voluptas in enim. Illum veritatis dignissimos atque quae porro quia molestias. Expedita ut ut minima et corrupti enim.",
        "product_price": "99",
        "product_stock": "99"	
    }
}     
```


