<template>
	<NcModal v-if="modalShow" :title="t('deck', 'Copy or move card to another board')" @close="modalShow=false">
		<div class="modal__content">
			<h3>{{ t('deck', 'Copy or move card to another board') }}</h3>
			<NcMultiselect v-model="selectedBoard"
				:track-by="id"
				:placeholder="t('deck', 'Select a board')"
				:options="activeBoards"
				:max-height="100"
				label="title"
				@select="loadStacksFromBoard" />
			<NcMultiselect v-model="selectedStack"
				:track-by="id"
				:placeholder="t('deck', 'Select a list')"
				:options="stacksFromBoard"
				:max-height="100"
				label="title">
				<span slot="noOptions">
					{{ t('deck', 'List is empty') }}
				</span>
			</NcMultiselect>

			<button :disabled="!isBoardAndStackChoosen" class="primary" @click="copyCard">
				{{ t('deck', 'Copy card') }}
			</button>
			<button :disabled="!isBoardAndStackChoosen" class="primary" @click="moveCard">
				{{ t('deck', 'Move card') }}
			</button>
			<button @click="modalShow=false">
				{{ t('deck', 'Cancel') }}
			</button>
		</div>
	</NcModal>
</template>

<script>
import { NcModal, NcMultiselect } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { subscribe, unsubscribe } from '@nextcloud/event-bus'

export default {
	name: 'CardMoveDialog',
	components: { NcModal, NcMultiselect },
	data() {
		return {
			card: null,
			modalShow: false,
			selectedBoard: '',
			selectedStack: '',
			stacksFromBoard: [],
		}
	},
	computed: {
		activeBoards() {
			return this.$store.getters.boards.filter((item) => item.deletedAt === 0 && item.archived === false)
		},
		isBoardAndStackChoosen() {
			return !(this.selectedBoard === '' || this.selectedStack === '')
		},
	},
	mounted() {
		subscribe('deck:card:show-move-dialog', this.openModal)
	},
	destroyed() {
		unsubscribe('deck:card:show-move-dialog', this.openModal)
	},
	methods: {
		openModal(card) {
			this.card = card
			this.preselectBoardAndStack().then(() => {
				this.modalShow = true
			});
		},
		async loadStacksFromBoard(board) {
			const currentStack = this.selectedStack
			this.selectedStack = ''

			try {
				const url = generateUrl('/apps/deck/stacks/' + board.id)
				const response = await axios.get(url)
				this.stacksFromBoard = response.data

				// try to set a stack with the same name or the first one or none
				this.selectedStack = this.stacksFromBoard.find((stack) => {
					return stack.title === currentStack.title
				}) || this.stacksFromBoard[0] || ''
			} catch (err) {
				this.selectedStack = currentStack
				return err
			}
		},
		async moveCard() {
			this.copiedCard = Object.assign({}, this.card)
			this.copiedCard.stackId = this.selectedStack.id
			this.$store.dispatch('moveCard', this.copiedCard)
			if (parseInt(this.boardId) === parseInt(this.selectedStack.boardId)) {
				await this.$store.commit('addNewCard', { ...this.copiedCard })
			}
			this.modalShow = false
		},

		async preselectBoardAndStack() {
			const stack = this.$store.getters.stackById(this.card.stackId)
			this.selectedBoard = this.$store.getters.boardById(stack.boardId)
			await this.loadStacksFromBoard(this.selectedBoard);
			this.selectedStack = this.stacksFromBoard.find(s => s.id === this.card.stackId)
		},
		async copyCard() {
			const copy = await this.$store.dispatch('copyCard', {
				id: this.card.id,
				stackId: this.selectedStack.id,
			})
			this.modalShow = false
			showUndo(t('deck', 'Card copied'), () => this.$store.dispatch('deleteCard', copy))
		},
	},
}
</script>

<style lang="scss" scoped>
.modal__content {
	min-width: 250px;
	min-height: 120px;
	text-align: center;
	margin: 20px 20px 100px 20px;

	.multiselect {
		margin-bottom: 10px;
	}
}

.modal__content button {
	float: right;
	margin-top: 50px;
}
</style>
