<?php
/**
 * @copyright Copyright (c) Michael Weimann <mail@michael-weimann.eu>
 *
 * @author Michael Weimann <mail@michael-weimann.eu>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Deck\Db;

use OCA\Deck\BadRequestException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/** @template-extends QBMapper<Category> */
class CategoryMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'deck_categories', Category::class);
	}

	public function find(int $id): Category | null {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('deck_categories')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		/** @var Category $category */
		return $this->findEntity($qb);
	}

	/**
	 * @return Category[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();
		return $qb->select('*')
			->from('deck_categories')
			->executeQuery()
			->fetchAll();
	}

	public function create(
		string $title,
		int $order = 1000,
		string $color = null,
	): Category {
		$this->validate($title, $order, $color);

		$category = new Category();
		$category->setTitle($title);
		$category->setOrder($order);
		$category->setColor($color);

		$this->insert($category);

		return $category;
	}

	public function updateById(
		int $categoryId,
		string $title,
		int $order = 1000,
		string $color = null,
	): Category {
		$category = $this->find($categoryId);

		if ($category === null) {
			throw new BadRequestException('category not found');
		}

		$this->validate($title, $order, $color);

		$category->setTitle($title);
		$category->setOrder($order);
		$category->setColor($color);

		$this->update($category);

		return $category;
	}

	private function validate(string $title, int $order = 1000, string $color = null): void {
		if (strlen($title) > 100) {
			throw new BadRequestException('title can have a max length of 100');
		}

		if ($order < 1 || $order > 99999) {
			throw new BadRequestException('order must be a value between 0 and 100000');
		}

		if ($color !== null && !preg_match('/^[a-f0-9]{6}$/i', $color)) {
			throw new BadRequestException('optional color must be 6 digit hex-code, e.g. 991b1b');
		}
	}

	public function deleteById(int $id): void {
		$category = $this->find($id);

		if ($category) {
			// unset category on boards
			$updateQuery = $this->db->getQueryBuilder();
			$updateQuery->update('deck_boards')
				->where($updateQuery->expr()->eq('category_id', $updateQuery->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
				->set('category_id', $updateQuery->createNamedParameter(null, IQueryBuilder::PARAM_NULL))
				->executeStatement();
			// then delete the category
			$this->delete($category);
		}
	}
}
