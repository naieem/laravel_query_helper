<?php
/**
 * Created by Naieem Mahmud Supto.
 * User: Naieem.mahmud
 * Date: 9/6/2018
 * Time: 3:06 PM
 */

/*

        $whereClause = [
                    "and" => [
        //                "eql"=>[
        //                    "product_title" => "Dolores mollitia."
        //                ],
        //                "gt" => [
        //                    "product_price" => "47"
        //                ]
                    ],
                    "or" => [
        //                "eql"=>[
        //                    "product_title" => "Dolores mollitia."
        //                ],
        //                "gt"=>[
        //                    "product_price" => "47"
        //                ]
                    ]
                ];
        $orderBy = [
            "product_price" => "desc"
        ];
        $pageNumber = 5;
        $itemPerPage = 5;
        $relationalFncs = ['metas', 'tags'];

 */

namespace App\QueryHelper;

use Illuminate\Support\Facades\App;

class QueryHelper
{
    private $className;
    private $defaultItemsPerPage = 10;
    private $conditionProperties = [
        "eql" => "=",
        "gt" => ">",
        "lt" => "<",
        "gte" => ">=",
        "lte" => "<="
    ];

    /**
     * Title: Construct functions
     * Description: takes class reference to make it available for the closure
     * @param abstruct class
     * @return
     */
    public function __construct($class)
    {
        $this->className = app()->make($class);
    }

    /**
     * Title: getAllData
     * Description: Getting all data
     * @param relational table functions
     * @return returned collection of data
     */
    public function getAllData($relationFncs = null)
    {
        $datas = $this->className->all();
        if (isset($relationFncs)) {
            $datas = $this->relationalData($datas, $relationFncs);
        }
        return $datas;
    }

    /**
     * Title:getByPagination
     * Description: getting data by pagination
     * @param perpageItem ,relational functions
     * @return
     */
    public function getByPagination($resultPerPage, $relationFncs = null)
    {
        $datas = $this->className->paginate($resultPerPage);
        if (count($relationFncs) > 0) {
            $datas = $this->relationalData($datas, $relationFncs);
        }
        return $datas;
    }

    /**
     * Title:getDataBySqlFilter
     * Description: getting data by sql filter and query
     * @param whereClause (array),Orderby(array),PageNumber(number),ItemPerPage(number),RelationalFunctions(string)
     * @return collection
     */
    public function getDataBySqlFilter($whereClause = [], $orderBy = [], $pageNumber = null, $itemPerPage = null, $relationalFncs = null)
    {
        $query = $this->className->newQuery();
        // -------------------------------------------
        // block for extending where clause starts
        // -------------------------------------------
        if (isset($whereClause) && !empty($whereClause) && count($whereClause) > 0) {
            $this->denormalizeWhereClause($query, $whereClause);
        }
        // -------------------------------------------
        // block for extending where clause ends
        // -------------------------------------------

        // -------------------------------------------
        // block for orderby clause starts
        // ------------------------------------------
        if (isset($orderBy) && !empty($orderBy)) {
            $this->prepareOrderQUery($query, $orderBy);
        }
        // -------------------------------------------
        // block for orderby clause ends
        // ------------------------------------------

        // -------------------------------------------
        // block for pagination starts
        // ------------------------------------------
        if (isset($pageNumber) && isset($itemPerPage)) {
            $this->preparePaginationQuery($query, $pageNumber, $itemPerPage);
        } else {
            $query->limit($this->defaultItemsPerPage);
        }
        // -------------------------------------------
        // block for pagination ends
        // ------------------------------------------

        // --------------------------------------------
        // block for getting relational datas starts
        if (isset($relationalFncs) && !empty($relationalFncs)) {
            $query->with($relationalFncs);
        }
        // --------------------------------------------
        // block for getting relational datas ends
        // --------------------------------------------
        $datas = $query->get();

        return [
            "results" => $datas,
            "totalCount" => count($datas)
        ];
    }

    /**
     * Title:getResultByJoin
     * Description: getting result by join and others complex query
     * @param parent table condition,child table condition, fields,orderby,pagenumber,item per page
     * @return collection
     */
    public function getResultByComplex($parentCondition = null, $joiningcondition = null, $fields = null, $orderby = null, $pageNumber = null, $itemPerPage = null)
    {
        $query = $this->className->newQuery();
        /* selecting field by raw query */
        $query->selectRaw($fields);
        // ----------------------------------------------------------
        // block for joining condition denormalizing starts
        // ----------------------------------------------------------
        if (isset($joiningcondition) && !empty($joiningcondition) && count($joiningcondition)) {
            $this->prepareJoinQuery($query, $joiningcondition);
        }
        // ----------------------------------------------------------
        // block for joining condition denormalizing ends
        // ----------------------------------------------------------

        // ---------------------------------------------------------
        // block for main table where clause denormalizing starts
        // ---------------------------------------------------------
        if (isset($parentCondition) && !empty($parentCondition) && count($parentCondition) > 0) {
            $this->denormalizeWhereClause($query, $parentCondition);
        }
        // --------------------------------------------------------
        // block for main table where clause denormalizing ends
        // --------------------------------------------------------

        // ------------------------------------------------
        // block for orderby query preparation starts
        // ------------------------------------------------
        if (isset($orderby) && !empty($orderby)) {
            $this->prepareOrderQUery($query, $orderby);
        }
        // ------------------------------------------------
        // block for orderby query preparation ends
        // ------------------------------------------------

        // -------------------------------------------
        // block for pagination starts
        // ------------------------------------------
        if (isset($pageNumber) && isset($itemPerPage)) {
            $this->preparePaginationQuery($query, $pageNumber, $itemPerPage);
        } else {
            $query->limit($this->defaultItemsPerPage);
        }
        // -------------------------------------------
        // block for pagination ends
        // ------------------------------------------

        $datas = $query->get();
        return [
            "results" => $datas,
            "totalCount" => count($datas)
        ];
    }

    /**
     * Inserting information to specific table
     * @param $tableName
     * @param $data
     * @return array
     */
    public function insert($tableName, $data)
    {
        try {
            $id = DB::table($tableName)->insertGetId($data);
            if ($id) {
                return [
                    'isSuccess' => true,
                    'insertedId' => $id
                ];
            }
        } catch (Exception $e) {
            return [
                'isSuccess' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Title:prepareJoinQuery
     * Description: preparing join query
     * @param model ,conditions
     * @return
     */
    public function prepareJoinQuery($query, $joiningcondition)
    {
        foreach ($joiningcondition as $key => $value) {
            foreach ($value as $conditionKey => $conditionValue) {
                $query->join($key, $conditionValue[0], $this->conditionProperties[$conditionKey], $conditionValue[1]);
            }
        }
    }

    /**
     * Title:denormalizeWhereClause
     * Description: Denormalizing where clause for preparing where conditional query
     * @param query model, $whereClause
     * @return
     */
    public function denormalizeWhereClause($query, $whereClause)
    {
        foreach ($whereClause as $key => $val) {
            if (!empty($val) && count($val) > 0) {
                foreach ($val as $andkey => $andval) {
                    $this->prepareWhereQuery($query, $key, $andkey, $andval);
                }
            }
        }

    }

    /**
     * Title:preparePaginationQuery
     * Description: preparing query for getting paginated data
     * @param $queryModel (laravel query model),pagenumber,itemsperpage
     * @return $queryModel
     */
    public function preparePaginationQuery($queryModel, $pageNumber, $itemPerPage)
    {
        $skippedItem = ($pageNumber - 1) * $itemPerPage;
        $queryModel->skip($skippedItem);
        $queryModel->take($itemPerPage);
        return $queryModel;
    }

    /**
     * Title:prepareWhereQuery
     * Description:Preparing where clause
     * @param $queryModel =['laravel model'],$queryType['and','or'],$conditionType ['eql','gt','gte' and etc],$conditionValues[$key=>$value pair]
     * @return $queryModel
     */
    public function prepareWhereQuery($queryModel, $queryType, $conditionType = '', $conditionValues = [])
    {

        if ($queryType == "and") {
            foreach ($conditionValues as $key => $val) {
                $queryModel->where($key, $this->conditionProperties[$conditionType], $val);
            }
        }
        if ($queryType == "or") {
            foreach ($conditionValues as $key => $val) {
                $queryModel->orWhere($key, $this->conditionProperties[$conditionType], $val);
            }
        }
        return $queryModel;
    }

    /**
     * Title: prepareOrderQUery
     * Description: Preparing model for executing order query
     * @param $queryModel =['laravel model'],$orderByProp[Order by property]
     * @return $queryModel
     */
    public function prepareOrderQUery($queryModel, $orderByProp)
    {
        foreach ($orderByProp as $key => $val) {
            $queryModel->orderBy($key, $val);
        }
        return $queryModel;
    }

    /**
     * Title:relationalData
     * Description: returning relational datas
     * @param existing collection(datas), relational functions
     * @return
     */
    public function relationalData($datas, $relationFncs)
    {
        foreach ($datas as $data) {
            foreach ($relationFncs as $relationFnc) {
                $data[$relationFnc];
            }
        }
        return $datas;
    }
}