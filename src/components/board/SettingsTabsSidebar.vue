<template>
	<div>
		<label for="settings-category">{{ t('deck', 'Category') }}</label>
		<NcMultiselect id="settings-category"
			v-model="category"
			track-by="id"
			label="title"
			:placeholder="t('deck', 'select category')"
			:options="selectCategories"
			:taggable="true"
			:clear-on-select="false"
			@tag="onNewCategory" />
	</div>
</template>
<script>
import { mapGetters } from 'vuex'
import { NcMultiselect } from '@nextcloud/vue'

let newCategoryId = 1

export default {
	name: 'SettingsTabsSidebar',
	components: {
		NcMultiselect,
	},
	props: {
		board: {
			type: Object,
			default: undefined,
		},
	},
	data() {
		return {
			uncategorised: {
				id: null,
				title: this.t('deck', 'uncategorised'),
			},
		}
	},
	computed: {
		...mapGetters(['categories']),
		selectCategories() {
			return [
				this.uncategorised,
				...this.categories,
			]
		},
		category: {
			get() {
				if (!this.board.categoryId) {
					return this.uncategorised
				}

				return this.categories.find((c) => {
					return c.id === this.board.categoryId
				})
			},
			async set(category) {
				const boardCopy = JSON.parse(JSON.stringify(this.board))

				if (category.id === null) {
					boardCopy.categoryId = null
				} else if (category.new) {
					const newCategory = await this.$store.dispatch('createCategory', {
						title: category.title,
					})
					boardCopy.categoryId = newCategory.id
				} else {
					boardCopy.categoryId = category.id
				}

				this.$store.dispatch('updateBoard', boardCopy)
				this.$store.dispatch('setCurrentBoard', boardCopy)
			},
		},
	},
	methods: {
		onNewCategory(category) {
			this.category = {
				new: true,
				id: `new-${newCategoryId++}`,
				title: category,
			}
		},
	},
}
</script>
