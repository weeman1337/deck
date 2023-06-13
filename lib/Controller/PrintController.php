<?php

namespace OCA\Deck\Controller;

use DateTime;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;
use OCA\Deck\Db\Assignment;
use OCA\Deck\Db\Board;
use OCA\Deck\Db\Card;
use OCA\Deck\Db\Stack;
use OCA\Deck\Service\BoardService;
use OCA\Deck\Service\CardService;
use OCA\Deck\Service\StackService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Comments\ICommentsManager;
use OCP\IRequest;
use OCP\IUserManager;

class PrintController extends Controller {

    /**
     * @var BoardService
     */
	private $boardService;

    /**
     * @var StackService
     */
    private $stackService;

    /**
     * @var CardService
     */
    private $cardService;

    /**
     * @var ICommentsManager
     */
    private $commentsManager;

    /**
     * @var IUserManager
     */
    private $userManager;

    /**
     * @var CommonMarkConverter
     */
    private $markdownConverter;

    public function __construct(
        $appName,
        IRequest $request,
        BoardService $service,
        StackService $stackService,
        ICommentsManager $commentsManager,
        IUserManager $userManager,
        CardService $cardService
    ) {
		parent::__construct($appName, $request);
		$this->boardService = $service;
        $this->stackService = $stackService;
        $this->commentsManager = $commentsManager;
        $this->userManager = $userManager;
        $this->cardService = $cardService;

        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());
        $this->markdownConverter = new MarkdownConverter($environment);
	}

    /**
	 * @NoAdminRequired
	 * @NoCSRFRequired
     */
	public function index($board) {

        if (empty($board)) {
            return $this->listBoards();
        }

        return $this->printBoard($board);

    }

    private function printBoard($boardId) {

		$response = new TemplateResponse('deck', 'print_board', [], TemplateResponse::RENDER_AS_BLANK);

        /** @var Board $board */
        $board = $this->boardService->find($boardId);

        /** @var Stack[] $stacks */
        $stacks = $this->stackService->findAll($boardId);

        $stacksData = array_map(
            [$this, 'mapStack'],
            $stacks
        );

        $response->setParams([
            'title' => $board->getTitle(),
            'stacks' => $stacksData,
            'css' => file_get_contents(__DIR__ . '/../../css/print_board.css'),
        ]);

        return $response;

    }

    /**
     * @param Stack $stack
     */
    private function mapStack($stack) {

        return [
            'title' => $stack->getTitle(),
            'cards' => array_map([$this, 'mapCard'], $stack->getCards()),
        ];

    }

    /**
     * @param Card $card
     */
    private function mapCard($card) {

        $this->cardService->enrich($card);

		$duedate = null;

		if ($card->getDuedate() !== null) {
            $dueDate = $card->getDuedate()->format('d.m.Y');
		}

        $description = $this->markdownConverter->convert($card->getDescription());

        return [
            'title' => $card->getTitle(),
            'description' => $description,
            'dueDate' => $dueDate,
            'hasAttachment' => $card->getAttachmentCount() > 0,
            'labels' => array_map(fn ($label) => $label->getTitle(), $card->getLabels()),
            'comments' => array_map(
                [$this, 'mapComment'],
                $this->commentsManager->getForObject('deckCard', $card->getId())
            ),
            'assignedUsers' => array_map(
                function (Assignment $assignment) {
                    switch ($assignment->getType()) {
                        case Assignment::TYPE_USER:
                            $user = $this->userManager->get($assignment->getParticipant());
                            return $user->getDisplayName();
                        case Assignment::TYPE_GROUP:
                            return $assignment->getParticipant() . ' (Gruppe)';
                        default:
                            return $assignment->getParticipant();
                    }
                },
                $card->getAssignedUsers(),
            ),
        ];

    }

    private function mapComment($comment) {

        $actor = $this->userManager->get($comment->getActorId());

        return [
            'message' => $comment->getMessage(),
            'creator' => $actor->getDisplayName(),
            'created' => $comment->getCreationDateTime()->format('d.m.Y, H:i'),
        ];

    }

    private function listBoards() {

		$response = new TemplateResponse('deck', 'print_list');

        $boards = $this->boardService->findAll(0);
        $boardsData = array_map(
            fn($board) => ['id' => $board->getId(), 'title' => $board->getTitle()],
            $boards
        );

        $response->setParams([
            'boards' => $boardsData,
        ]);

        return $response;

    }
}
