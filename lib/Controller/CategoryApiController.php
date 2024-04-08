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

use OCA\Deck\Db\CategoryMapper;
use OCA\Deck\StatusException;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class CategoryApiController extends ApiController {
	/**
	 * @param string $appName
	 */
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
	 * @CORS
	 * @NoCSRFRequired
	 *
	 * Return all categories
	 *
	 * @throws StatusException
	 */
	public function index() {
		$categories = $this->categoryMapper->findAll();
		$response = new DataResponse($categories, Http::STATUS_OK);
		return $response;
	}

	/**
 	 * @NoAdminRequired
	 * @CORS
	 * @NoCSRFRequired
	 */
	public function delete() {
		$categoryId = $this->request->getParam('categoryId');
		$this->categoryMapper->deleteById($categoryId);
		return new DataResponse([], Http::STATUS_OK);
	}

	/**
 	 * @NoAdminRequired
	 * @CORS
	 * @NoCSRFRequired
	 */
	public function create(
		string $title,
		int $order = 1000,
		string $color = null,
	) {
		$category = $this->categoryMapper->create($title, $order, $color);
		return new DataResponse($category, Http::STATUS_OK);
	}

	/**
 	 * @NoAdminRequired
	 * @CORS
	 * @NoCSRFRequired
	 */
	public function update(
		string $title,
		int $order = 1000,
		string $color = null,
	) {
		$categoryId = $this->request->getParam('categoryId');
		$category = $this->categoryMapper->updateById($categoryId, $title, $order, $color);
		return new DataResponse($category, Http::STATUS_OK);
	}
}
