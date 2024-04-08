<?php
/**
 * @copyright Copyright (c) 2024 Michael Weimann <mail@michael-weimann.eu>
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

namespace OCA\Deck\Controller;

use OCA\Deck\Db\Category;
use OCA\Deck\Db\CategoryMapper;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class CategoryController extends Controller {
	public function __construct(
		$appName,
		IRequest $request,
		private CategoryMapper $categoryMapper,
		private $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(
		string $title,
		int $order = 1000,
		string $color = null,
	): Category {
		return $this->categoryMapper->create($title, $order, $color);
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(
		string $title,
		int $order = 1000,
		string $color = null,
	): Category {
		$categoryId = $this->request->getParam('categoryId');
		return $this->categoryMapper->updateById($categoryId, $title, $order, $color);
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(): void {
		$categoryId = $this->request->getParam('categoryId');
		$this->categoryMapper->deleteById($categoryId);
	}
}
