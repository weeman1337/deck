/*
 * @copyright Copyright (c) 2018 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import Vue from 'vue'
import Router from 'vue-router'
import { generateUrl } from '@nextcloud/router'
import { BOARD_FILTERS } from './store/main.js'
import Boards from './components/boards/Boards.vue'
import Board from './components/board/Board.vue'
import Sidebar from './components/Sidebar.vue'
import BoardSidebar from './components/board/BoardSidebar.vue'
import CardSidebar from './components/card/CardSidebar.vue'
import Overview from './components/overview/Overview.vue'

Vue.use(Router)

const router = new Router({
	mode: 'history',
	base: generateUrl('/apps/deck/'),
	linkActiveClass: 'active',
	routes: [
		{
			path: '/',
			name: 'main',
			component: Overview,
		},
		{
			path: '/overview/:filter',
			name: 'overview',
			components: {
				default: Overview,
			},
			props: {
				default: (route) => {
					return {
						filter: route.params.filter,
					}
				},
			},
		},
		{
			path: '/board',
			name: 'boards',
			component: Boards,
			props: {
				navFilter: BOARD_FILTERS.ALL,
			},
		},
		{
			path: '/board/category/:category',
			name: 'boards.category',
			component: Boards,
			props: route => ({
				navFilter: BOARD_FILTERS.CATEGORY,
				navFilterCategory: decodeURIComponent(route.params.category),
			}),
		},
		{
			path: '/board/archived',
			name: 'boards.archived',
			component: Boards,
			props: {
				navFilter: BOARD_FILTERS.ARCHIVED,
			},
		},
		{
			path: '/board/shared',
			name: 'boards.shared',
			component: Boards,
			props: {
				navFilter: BOARD_FILTERS.SHARED,
			},
		},
		{
			path: '/board/:id',
			name: 'board',
			components: {
				default: Board,
				sidebar: Sidebar,
			},
			props: {
				default: (route) => {
					return {
						id: parseInt(route.params.id, 10),
					}
				},
			},
			children: [
				{
					path: 'details',
					name: 'board.details',
					components: {
						default: Boards,
						sidebar: BoardSidebar,
					},
					props: {
						default: (route) => {
							return {
								id: parseInt(route.params.id, 10),
							}
						},
						sidebar: (route) => {
							return {
								id: parseInt(route.params.id, 10),
							}
						},
					},
				},
				{
					path: 'card/:cardId/:tabId?/:tabQuery?',
					name: 'card',
					components: {
						sidebar: CardSidebar,
					},
					props: {
						default: (route) => {
							return {
								cardId: parseInt(route.params.cardId, 10),
							}
						},
						sidebar: (route) => {
							return {
								id: parseInt(route.params.cardId, 10),
								tabId: route.params.tabId,
								tabQuery: route.params.tabQuery,
							}
						},
					},
				},
			],
		},
		// redirects to keep compatibility to 1.0.0 routes
		{
			path: '/boards/:id',
			redirect: '/board/:id',
		},
		{
			path: '/boards/:id/cards/:cardId',
			redirect: '/board/:id/card/:cardId',
		},
		{
			path: '/!/board/:id',
			redirect: '/board/:id',
		},
		{
			path: '/!/board/:id/card/:cardId',
			redirect: '/board/:id/card/:cardId',
		},
	],
})

router.beforeEach((to, from, next) => {
	// Redirect if fullPath begins with a hash (ignore hashes later in path)
	if (to.fullPath.substring(0, 2) === '/#') {
		const path = to.fullPath.substring(2)
		next(path)
		return
	}
	next()
})

export default router
