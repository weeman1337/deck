<!--
  - @copyright Copyright (c) 2024 Michael Weimann <mail@michael-weimann.eu>
  -
  - @author Michael Weimann <mail@michael-weimann.eu>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
	<NcModal :name="t('deck', 'Edit category')"
		:can-close="!saving"
		@close="$emit('close')">
		<div class="deck-edit-category-modal-content">
			<h2>{{ t('deck', 'Edit category') }}</h2>
			<div class="deck-edit-category-modal-fields">
				<div>
					<NcTextField :value.sync="editCategory.title"
						maxlength="100"
						:label="t('deck', 'Title')"
						:label-visible="true" />
					<div v-if="titleError"
						class="deck-edit-category-modal-error">
						{{ titleError }}
					</div>
				</div>
				<div class="deck-edit-category-modal-color-field">
					<label class="deck-edit-category-modal-color-label">
						{{ t('deck', 'Color') }}
					</label>
					<div class="deck-edit-category-modal-color-picker">
						<div :style="{'background-color': color}"
							class="deck-edit-category-modal-color-value" />
						<NcColorPicker v-model="color">
							<NcButton>
								<template #icon>
									<Pencil :size="20" />
								</template>
							</NcButton>
						</NcColorPicker>
						<NcButton v-if="editCategory.color"
							@click="clearColor()">
							<template #icon>
								<Delete :size="20" />
							</template>
						</NcButton>
					</div>
				</div>
				<div>
					<NcInputField type="number"
						min="1"
						max="99999"
						:value.sync="editCategory.order"
						:label="t('deck', 'Sort')"
						:label-visible="true" />
					<div v-if="orderError"
						class="deck-edit-category-modal-error">
						{{ orderError }}
					</div>
				</div>
			</div>
			<div class="deck-edit-category-modal-buttons">
				<NcButton class="deck-edit-category-modal-buttons-delete"
					type="error"
					:disabled="saving"
					@click="handleDelete()">
					{{ t('deck', 'Delete') }}
				</NcButton>
				<NcButton type="secondary"
					:disabled="saving"
					@click="handleCancel()">
					{{ t('deck', 'Cancel') }}
				</NcButton>
				<NcButton type="primary"
					:disabled="saving"
					@click="handleSave()">
					{{ t('deck', 'Save') }}
				</NcButton>
			</div>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcColorPicker, NcModal, NcTextField } from '@nextcloud/vue'
import NcInputField from '@nextcloud/vue/dist/Components/NcInputField.js'
import Delete from 'vue-material-design-icons/Delete.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import { mapGetters } from 'vuex'

export default {
	name: 'EditCategoryModal',
	components: {
		Delete,
		NcButton,
		NcColorPicker,
		NcInputField,
		NcModal,
		NcTextField,
		Pencil,
	},
	props: {
		category: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			editCategory: { ...this.category },
			titleError: null,
			orderError: null,
			saving: false,
		}
	},
	computed: {
		...mapGetters([
			'categories',
			'nonArchivedBoardsByCategory',
		]),
		color: {
			get() {
				if (!this.editCategory.color) {
					return 'currentColor'
				}

				return `#${this.editCategory.color}`
			},
			set(color) {
				this.editCategory.color = color.substring(1)
			},
		},
	},
	methods: {
		clearColor() {
			this.editCategory.color = null
		},
		handleCancel() {
			this.$emit('close')
		},
		async handleDelete() {
			this.saving = true

			// update boards that have that category
			for (const board of this.nonArchivedBoardsByCategory[this.editCategory.id]) {
				const boardCopy = JSON.parse(JSON.stringify(board))
				boardCopy.categoryId = null
				await this.$store.dispatch('updateBoard', boardCopy)
			}

			await this.$store.dispatch('deleteCategory', this.editCategory)
			this.$emit('close')
		},
		async handleSave() {
			if (this.editCategory.title === this.category.title
				&& this.editCategory.order === this.category.order
				&& this.editCategory.color === this.category.color) {
				// no change
				this.$emit('close')
				return
			}

			const inputOrder = parseInt(this.editCategory.order, 10)

			if (!inputOrder || inputOrder < 1 || inputOrder > 99999) {
				this.orderError = this.t('deck', 'Sort must be a value from 1 to 99999')
			} else {
				this.orderError = null
			}

			const titleLength = this.editCategory.title.trim().length

			if (titleLength === 0) {
				this.titleError = this.t('deck', 'Please fill in the title')
			} else if (titleLength > 100) {
				this.titleError = this.t('deck', 'Title should have at max 100 chars')
			} else if (this.editCategory.title !== this.category.title
			   && this.categories.some((c) => c.title.trim() === this.editCategory.title.trim())) {
				this.titleError = this.t('deck', 'Title already in use')
			} else {
				this.titleError = null
			}

			if (this.orderError || this.titleError) {
				return
			}

			this.saving = true

			const toSave = {
				id: this.editCategory.id,
				title: this.editCategory.title,
				order: inputOrder,
				color: this.editCategory.color,
			}

			try {
				await this.$store.dispatch('updateCategory', toSave)
				this.$emit('close')
			} catch (error) {
				// ignore
			}
		},
	},
}

</script>

<style scoped>
	.deck-edit-category-modal-content {
		padding: 30px 40px;
	}

	.deck-edit-category-modal-fields {
		display: flex;
		flex-direction: column;
		gap: 8px;
		margin-bottom: 32px;
	}

	.deck-edit-category-modal-color-label {
		display: block;
		padding: 4px 0;
	}

	.deck-edit-category-modal-color-picker {
		align-items: center;
		display: flex;
		gap: 8px;
	}

	.deck-edit-category-modal-color-value {
		align-self: stretch;
		border-radius: 4px;
		width: 100px;
	}

	.deck-edit-category-modal-buttons {
		display: flex;
		gap: 16px;
	}

	.deck-edit-category-modal-buttons-delete {
		margin-right: auto;
	}

	.deck-edit-category-modal-error {
		color: red;
		font-size: .9rem;
	}
</style>
