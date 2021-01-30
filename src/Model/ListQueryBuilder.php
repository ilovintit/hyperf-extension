<?php
declare(strict_types=1);

namespace Iit\HyLib\Model;

use Carbon\Carbon;
use Closure;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Collection;
use Psr\Log\InvalidArgumentException;

/**
 * Class ListQueryBuilder
 * @package Iit\HyLib\Model
 */
class ListQueryBuilder
{
    const ORDER_TYPE_ASC = 'asc';

    const ORDER_TYPE_DESC = 'desc';

    /**
     * @var Builder
     */
    private Builder $baseQuery;

    /**
     * @var Builder
     */
    private Builder $query;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var string
     */
    private string $pageKey = 'X-Page';

    /**
     * @var string
     */
    private string $perPageKey = 'X-Per-Page';

    /**
     * @var string
     */
    private string $orderFieldKey = 'X-Order-Field';

    /**
     * @var string
     */
    private string $orderTypeKey = 'X-Order-Type';

    /**
     * @var string
     */
    private string $searchKeywordKey = 'X-Search-Keywords';

    /**
     * @var integer
     */
    private int $perPage;

    /**
     * @var integer
     */
    private int $page;

    /**
     * @var bool
     */
    private bool $withPage = false;

    /**
     * @var array
     */
    private array $searchRules = [];

    /**
     * @var bool
     */
    private bool $isEmptySearch = false;

    /**
     * @var bool
     */
    private bool $isCountBaseQuery = false;

    /**
     * @var array
     */
    private array $hidden = [];

    /**
     * @var array
     */
    private array $append = [];

    /**
     * @var array
     */
    private array $visible = [];

    /**
     * @var array
     */
    private array $needField = [];

    /**
     * @var array
     */
    private array $searchKeywords = [];


    /**
     * ListQueryBuilder constructor.
     * @param Builder $query
     * @param RequestInterface $request
     * @param array $configs
     */
    public function __construct(Builder $query, RequestInterface $request, $configs = [])
    {
        $this->setRequest($request);
        $this->setBaseQuery($query);
        $this->setQuery($query);
        $this->resolveConfigs($configs);
    }

    /**
     * @param Builder $query
     * @param array $configs
     * @return ListQueryBuilder
     */
    public static function create(Builder $query, $configs = []): ListQueryBuilder
    {
        return make(self::class, ['query' => $query, 'configs' => $configs]);
    }

    /**
     * @param $configs
     */
    protected function resolveConfigs(array $configs)
    {
        collect([
            'pageKey', 'perPageKey', 'orderFieldKey', 'orderTypeKey', 'searchKeywordKey'
        ])->each(function ($key) use ($configs) {
            $this->$key = isset($configs[$key]) ? $configs[$key] : $this->$key;
        });
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request): ListQueryBuilder
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @param Builder $query
     * @return $this
     */
    public function setQuery(Builder $query): ListQueryBuilder
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = $this->query;
        if ($this->withPage === true) {
            $query->limit($this->perPage)->offset(($this->page - 1) * $this->perPage);
        }
        return $query;
    }

    /**
     * @return Builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * @param Builder $query
     * @return $this
     */
    public function setBaseQuery(Builder $query): ListQueryBuilder
    {
        $this->baseQuery = $query;
        return $this;
    }

    /**
     * @return Builder
     */
    public function getBaseQuery(): Builder
    {
        return $this->baseQuery;
    }

    /**
     * @param array $searchKeywords
     * @return $this
     */
    public function setSearchKeywords(array $searchKeywords): ListQueryBuilder
    {
        $this->searchKeywords = $searchKeywords;
        return $this;
    }

    /**
     * @return array
     */
    public function getSearchKeywords()
    {
        if (empty($this->searchKeywords)) {
            $this->searchKeywords = $this->getSearchKeywordsFromRequest();
        }
        return $this->searchKeywords;
    }

    /**
     * @param $key
     * @param $value
     * @return ListQueryBuilder
     */
    public function addSearchKeyword($key, $value): ListQueryBuilder
    {
        $searchKeywords = $this->getSearchKeywords();
        $searchKeywords[$key] = $value;
        $this->setSearchKeywords($searchKeywords);
        return $this;
    }

    /**
     * @param $key
     * @return ListQueryBuilder
     */
    public function removeSearchKeyword($key): ListQueryBuilder
    {
        $searchKeywords = $this->getSearchKeywords();
        unset($searchKeywords[$key]);
        $this->setSearchKeywords($searchKeywords);
        return $this;
    }

    /**
     * @param int $defaultPerPage
     * @param int $defaultPage
     * @return $this
     */
    public function withPage($defaultPerPage = 10, $defaultPage = 1): ListQueryBuilder
    {
        $this->perPage = !empty($this->request->getHeaderLine($this->perPageKey)) ? $this->request->getHeaderLine($this->perPageKey) : $defaultPerPage;
        $this->page = !empty($this->request->getHeaderLine($this->pageKey)) ? $this->request->getHeaderLine($this->pageKey) : $defaultPage;
        $this->withPage = true;
        return $this;
    }

    /**
     * @param string $defaultOrderField
     * @param string $defaultOrderType
     * @param array $allowOrderFiled
     * @return $this
     */
    public function withOrder(string $defaultOrderField, $defaultOrderType = self::ORDER_TYPE_DESC, array $allowOrderFiled = []): ListQueryBuilder
    {
        if (!in_array($defaultOrderType, [self::ORDER_TYPE_ASC, self::ORDER_TYPE_DESC])) {
            throw new InvalidArgumentException('default order type is valid.');
        }
        $orderField = !empty($this->request->getHeaderLine($this->orderFieldKey)) ? $this->request->getHeaderLine($this->orderFieldKey) : $defaultOrderField;
        $allowField = !empty($allowOrderFiled) ? $this->checkOrderFieldIsAllow($allowOrderFiled, $orderField) : $orderField;
        $orderType = !empty($this->request->getHeaderLine($this->orderTypeKey)) ? $this->request->getHeaderLine($this->orderTypeKey) : $defaultOrderType;
        $this->query->orderBy($allowField, $orderType);
        return $this;
    }

    /**
     * @param $allowOrderFiled
     * @param $orderField
     * @return null
     */
    protected function checkOrderFieldIsAllow($allowOrderFiled, $orderField)
    {
        foreach ($allowOrderFiled as $key => $value) {
            $diffFiled = is_numeric($key) ? $value : $key;
            if ($orderField === $diffFiled) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @param array $searchRules
     * @param bool $allowEmpty
     * @param Closure|null $customQuery
     * @return $this
     */
    public function withSearch(array $searchRules, $allowEmpty = true, Closure $customQuery = null): ListQueryBuilder
    {
        if ($this->checkSearchKeywordsIsAllEmpty() && $allowEmpty === false) {
            $this->isEmptySearch = true;
            return $this;
        }
        $this->query = SearchKeyword::query($this->getSearchKeywords(), $this->query, $searchRules, $customQuery);
        return $this;
    }

    /**
     * @return array
     */
    protected function getSearchKeywordsFromRequest(): array
    {
        $searchKeywords = !empty($this->request->getHeaderLine($this->searchKeywordKey)) ? $this->request->getHeaderLine($this->searchKeywordKey) : null;
        $searchKeywords = !empty($searchKeywords) ? json_decode(base64_decode($searchKeywords), true) : [];
        return $searchKeywords;
    }

    /**
     * @return bool
     */
    protected function checkSearchKeywordsIsAllEmpty(): bool
    {
        $searchKeywords = $this->getSearchKeywords();
        if (empty($searchKeywords)) {
            return true;
        }
        return !(collect($this->searchRules)->count() === collect($this->searchRules)->filter(function ($rule) use ($searchKeywords) {
                if (!is_array($rule['key']) && !isset($rule['value'])) {
                    return SearchKeyword::checkValueIssetAndEmpty($searchKeywords, $rule['key']);
                }
                if (!is_array($rule['key']) && isset($rule['value'])) {
                    $value = $rule['value'] instanceof Closure ? $rule['value']($searchKeywords) : $rule['value'];
                    return $value !== null;
                }
                return collect($rule['key'])->count() === collect($rule['key'])->filter(function ($value, $key) use ($searchKeywords) {
                        $readKey = is_numeric($key) ? $value : $key;
                        return SearchKeyword::checkValueIssetAndEmpty($searchKeywords, $readKey);
                    });
            })->count());
    }

    /**
     * @param array $needField
     * @return $this
     */
    public function withFilterField(array $needField): ListQueryBuilder
    {
        $this->needField = $needField;
        return $this;
    }

    /**
     * @param array $hidden
     * @return $this
     */
    public function withHidden(array $hidden): ListQueryBuilder
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * @param array $append
     * @return $this
     */
    public function withAppends(array $append): ListQueryBuilder
    {
        $this->append = $append;
        return $this;
    }

    /**
     * @param array $visible
     * @return ListQueryBuilder
     */
    public function withVisible(array $visible): ListQueryBuilder
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return $this
     */
    public function withCountBaseQuery(): ListQueryBuilder
    {
        $this->isCountBaseQuery = true;
        return $this;
    }

    /**
     * @return array|Builder[]|Collection
     */
    public function get()
    {
        if ($this->isEmptySearch) {
            return [];
        }
        return $this->query()->get();
    }

    /**
     * @return array|LengthAwarePaginatorInterface
     */
    public function paginate()
    {
        if ($this->isEmptySearch) {
            return [];
        }
        return $this->query->paginate($this->perPage, ['*'], 'Page', $this->page);
    }

    /**
     * @param $list
     * @return array
     */
    protected function convertList($list): array
    {
        return array_map(function (Model $item) {
            if (!empty($this->hidden)) {
                $item->makeHidden($this->hidden);
            }
            if (!empty($this->append)) {
                $item->setAppends($this->append);
            }
            if (!empty($this->visible)) {
                $item->makeVisible($this->visible);
            }
            if (empty($this->needField)) {
                return $item->toArray();
            }
            return array_combine(array_map(function ($field) {
                return is_array($field) ? $field['key'] : $field;
            }, $this->needField), array_map(function ($field) use ($item) {
                if (is_string($field)) {
                    return $item[$field] instanceof Carbon ? $item[$field]->toDateString() : $item[$field];
                } elseif (is_array($field)) {
                    return $item[$field['key']] instanceof Carbon ? $item[$field['key']]->format($field['format']) : $item[$field['key']];
                } else {
                    return null;
                }
            }, $this->needField));
        }, collect($list)->all());
    }

    /**
     * @return int
     */
    public function countBaseQuery(): int
    {
        return $this->baseQuery->count();
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->convertList($this->get());
    }

    /**
     * @return array
     */
    public function paginateList(): array
    {
        $pageList = $this->paginate();
        $returnList = [
            'data' => empty($pageList) ? [] : $this->convertList($pageList->items()),
            'currentPage' => empty($pageList) ? 1 : $pageList->currentPage(),
            'total' => empty($pageList) ? 0 : $pageList->total(),
            'perPage' => empty($pageList) ? $this->perPage : $pageList->perPage(),
            'lastPage' => empty($pageList) ? 1 : $pageList->lastPage()
        ];
        if ($this->isCountBaseQuery) {
            $returnList['baseTotal'] = $this->countBaseQuery();
        }
        return $returnList;
    }
}
