<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\AbstractModel;
use helper_plugin_sqlite;

/**
 * Class AbstractService
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractService {

    protected $table;
    /** @var string|AbstractModel */
    protected $modelClassName;

    protected $sqlite;

    public function __construct(helper_plugin_sqlite $sqlite, string $table, string $modelClassName) {
        $this->table = $table;
        $this->sqlite = $sqlite;
        $this->modelClassName = $modelClassName;
    }

    public function getById(int $id): ?AbstractModel {
        $res = $this->sqlite->query('SELECT * FROM ' . $this->table . ' WHERE ' . $this->table . '_id = ?', (int)$id)->fetch();
        if ($res) {
            return ($this->modelClassName)::createFromArray($this->sqlite, $res);
        }
        return null;
    }

    public function create(array $data): bool {
        $sql = 'INSERT INTO ' . $this->table . ' (' . join(',', array_keys($data)) . ')  VALUES( ' . join(',', array_fill(0, count($data), '?')) . ')';

        return (bool)$this->sqlite->query($sql,
            ...array_values($data)
        );
    }

    public function update(AbstractModel $model, array $data): void {
        $sql = 'UPDATE ' . $this->table . ' SET ' . join(',', array_map(function ($key) {
                return $key . '=?';
            }, array_keys($data))) . ' WHERE ' . $this->table . '_id' . '=?';
        $this->sqlite->query($sql,
            ...array_values($data),
            ...[
                $model->{$this->table . 'Id'},
            ]
        );
    }

    public function getMaxId(): int {
        $res = $this->sqlite->query('SELECT max(' . $this->table . '_id) FROM ?', $this->table);
        return (int)$this->sqlite->res2single($res);
    }

    public function getAll(): array {
        $models = [];
        $res = $this->sqlite->query('SELECT * FROM ?', $this->table);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = ($this->modelClassName)::createFromArray($this->sqlite, $row);
        }
        return $models;
    }

}
