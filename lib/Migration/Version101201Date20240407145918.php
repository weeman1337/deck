<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2024 Michael Weimann <mail@michael-weimann.eu>
 *
 * @author Michael Weimann <mail@michael-weimann.eu>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Deck\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Creates the board categories table.
 */
class Version101201Date20240407145918 extends SimpleMigrationStep {
	/** @var IDBConnection */
	private $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('deck_categories') === true) {
			// table already exists
			return $schema;
		}

		$categoriesTable = $schema->createTable('deck_categories');
		$categoriesTable->addColumn('id', 'integer', [
			'autoincrement' => true,
			'notnull' => true,
			'length' => 4,
		]);
		$categoriesTable->addColumn('title', 'string', [
			'notnull' => true,
			'length' => 100,
		]);
		$categoriesTable->addColumn('color', 'string', [
			'notnull' => false,
			'length' => 6,
		]);
		$categoriesTable->addColumn('order', 'integer', [
			'notnull' => true,
			'length' => 4,
		]);
		$categoriesTable->setPrimaryKey(['id']);
		$categoriesTable->addUniqueIndex(['title'], 'deck_categories_name_idx');

		$boardsTable = $schema->getTable('deck_boards');
		$boardsTable->addColumn('category_id', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);
		$boardsTable->addIndex(['category_id'], 'deck_boards_category_id_idx');

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		$query = $this->db->getQueryBuilder();

		$categories = $query->select('category')
			->from('deck_boards')
			->executeQuery()
			->fetchAll();

		foreach ($categories as $categoryRecord) {
			$category = $categoryRecord['category'];

			if (empty($category)) {
				// this board does not have a category, skip
				continue;
			}

			$queryBuilder = $this->db->getQueryBuilder();
			$existingCategoryIdRaw = $queryBuilder->select('id')
				->from('deck_categories')
				->where($queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($category)))
				->executeQuery()
				->fetchOne();
			$categoryId = (int) $existingCategoryIdRaw;

			if (empty($categoryId) === true) {
				// insert category record
				$queryBuilder = $this->db->getQueryBuilder();
				$queryBuilder->insert('deck_categories')
					->setValue('title', $queryBuilder->createNamedParameter($category))
					->setValue('order', $queryBuilder->createNamedParameter(1000, IQueryBuilder::PARAM_INT))
					->executeStatement();
				$categoryId = $queryBuilder->getLastInsertId();
			}

			// set the category ID
			$updateQuery = $this->db->getQueryBuilder();
			$updateQuery->update('deck_boards')
				->where($updateQuery->expr()->eq('category', $updateQuery->createNamedParameter($category)))
				->set('category_id', $updateQuery->createNamedParameter($categoryId, IQueryBuilder::PARAM_INT))
				->executeStatement();
		}
	}
}
