<?php namespace osms\messaging\views;

use \osms\messaging\models;
use \osms\messaging\forms;


class FloodError extends \Exception
{}


class Create extends \osmf\View\Transaction
{
	protected function createMessage($form, $sender, $recipient)
	{
		$last_sent = models\Message::query()
			->where('sender', 'eq', $sender)
			->orderBy('-timestamp')
			->limit(1)
			->all();

		if ($last_sent) {
			$last_sent = $last_sent[0];
			$interval = $last_sent->timestamp->diff(new \DateTime());

			if (!($interval->y or $interval->m or $interval->d or $interval->h)) {
				// Interval is less than one day
				$seconds = $interval->i * 60 + $interval->s;
				if ($seconds < \osmf\Config::get('message_flood_limit')) {
					throw new FloodError();
				}
			}
		}

		$message = new models\Message();
		$message->sender = $sender;
		$message->recipient = $recipient;
		$message->subject = $form->cleaned_data['subject'];
		$message->body = $form->cleaned_data['body'];
		$message->save();

		$file = $form->cleaned_data['attachment'];

		if ($file) {
			$path = join_paths(
				\osmf\Config::get('upload_dir'),
				sprintf('%d-%s', $message->id, $file['name'])
			);
			$file['file']->moveUploadedFileTo($path);

			$message->attachment = $file['file'];
			$message->save();
		}

		return $message;
	}

	protected function render_GET($request, $args)
	{
		$this->context->error = FALSE;
		$this->context->flood = FALSE;
		$this->context->form = new forms\Message();
		return $this->renderResponse('messaging/create.html');
	}

	protected function render_POST($request, $args)
	{
		$form = new forms\Message($request->POST, $request->FILES);
		$this->context->flood = FALSE;

		if ($form->isValid()) {
			$this->context->error = FALSE;

			try {
				$recipient = $request->user->getModel()->cro;
				$this->createMessage(
					$form,
					$request->user->getModel(),
					$recipient
				);
				
				$this->message("Your message to $recipient->username was correctly sent!");
				$this->logger->logNotice("Message correctly sent to $recipient->username");

				return $this->redirect($this->reverse('inbox'));
			} catch (FloodError $e) {
				$this->logger->logWarn("Enforcing flood limits on user");
				$this->context->flood = TRUE;
			}
		} else {
			$this->logger->logWarn("Invalid data submitted to the message creation form");
		}

		$this->context->reply = FALSE;
		$this->context->error = TRUE;
		$this->context->form = $form;
		$this->setDbError();
		return $this->renderResponse('messaging/create.html');
	}
}


class Reply extends Create
{
	protected function render_GET($request, $args)
	{
		$message = models\Message::query()
			->where('recipient', 'eq', $request->user->getModel())
			->and('id', 'eq', $args['id'])
			->one();

		$subject = $message->subject;
		if (!startswith($subject, 'RE: ')) {
			$subject = 'RE: ' . $subject;
		}
		$form = new forms\Message();
		
		$this->context->subject = $subject;
		$this->context->prev = $message;
		$this->context->error = FALSE;
		$this->context->flood = FALSE;
		$this->context->form = $form;
		return $this->renderResponse('messaging/create.html');
	}

	protected function render_POST($request, $args)
	{
		$message = models\Message::query()
			->where('recipient', 'eq', $request->user->getModel())
			->and('id', 'eq', $args['id'])
			->one();

		$subject = $message->subject;
		if (!startswith($subject, 'RE: ')) {
			$subject = 'RE: ' . $subject;
		}

		$this->context->flood = FALSE;

		$data = $request->POST;
		$data['subject'] = $subject;
		$form = new forms\Message($data, $request->FILES);

		if ($form->isValid()) {
			$this->context->error = FALSE;

			try {
				$recipient = $message->sender;
				$this->createMessage(
					$form,
					$request->user->getModel(),
					$recipient
				);

				$this->message("Your reply to $recipient->username was correctly sent!");
				$this->logger->logNotice("Reply correctly sent to $recipient->username");

				return $this->redirect($this->reverse('inbox'));
			} catch (FloodError $e) {
				$this->logger->logWarn("Enforcing flood limits on user");
				$this->context->flood = TRUE;
			}
		} else {
			$this->logger->logWarn("Invalid data submitted to the message creation form");
		}

		$this->context->subject = $subject;
		$this->context->prev = $message;
		$this->context->error = TRUE;
		$this->context->form = $form;
		$this->setDbError();
		return $this->renderResponse('messaging/create.html');
	}
}


class Read extends \osmf\View\Transaction
{
	protected function render_GET($request, $args)
	{
		$message = models\Message::query()
			->where('recipient', 'eq', $request->user->getModel())
			->and('id', 'eq', $args['id'])
			->one();

		// TODO: Raise 404 if not found

		if ($message->status === 'unread') {
			$this->logger->logNotice("Marking message with ID $message->id as read");

			$message->status = 'read';
			$message->save();
		}

		$this->context->message = $message;

		return $this->renderResponse('messaging/read.html');
	}
}


class Inbox extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		$page = \array_get($request->GET, 'page', 1);
		$count = \osmf\Config::get('messages_per_page');
		
		$query = models\Message::query()
			->where('recipient', 'eq', $request->user->getModel())
			->and('status', 'eq', 'read')
			->or('recipient', 'eq', $request->user->getModel())
			->and('status', 'eq', 'unread')
			->orderBy('-timestamp');

		$paginator = new \osmf\Paginator($query, $count);
		$messages = $paginator->getPage($page);

		if (!$messages and $page > 1) {
			return $this->redirect($this->reverse('inbox'));
		}

		$this->context->messages = $messages;
		$this->context->paginator = $paginator;
		$this->context->page = $page;

		return $this->renderResponse('messaging/list.html');
	}
}


class Archives extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		$page = \array_get($request->GET, 'page', 1);
		$count = \osmf\Config::get('messages_per_page');

		$query = models\Message::query()
			->where('recipient', 'eq', $request->user->getModel())
			->and('status', 'eq', 'archived')
			->orderBy('-timestamp');

		$paginator = new \osmf\Paginator($query, $count);
		$messages = $paginator->getPage($page);

		if (!$messages and $page > 1) {
			return $this->redirect($this->reverse('inbox'));
		}

		$this->context->messages = $messages;
		$this->context->paginator = $paginator;
		$this->context->page = $page;

		return $this->renderResponse('messaging/archive.html');
	}
}


class Archive extends \osmf\View\Transaction
{
	protected function getMessage($user, $id)
	{
		// TODO Catch exeption and render 404 (middleware?)
		return models\Message::query()
			->where('recipient', 'eq', $user)
			->and('id', 'eq', $id)
			->and('status', 'neq', 'archived')
			->one();
	}

	protected function render_GET($request, $args)
	{
		$message = $this->context->message = $this->getMessage(
			$request->user->getModel(), $args['id']
		);

		$this->context->delete_attachment = (
			$message->attachment and isset($request->GET['delete-attachment'])
		);

		return $this->renderResponse('confirm.html');
	}

	protected function render_POST($request, $args)
	{
		$message = $this->getMessage(
			$request->user->getModel(), $args['id']
		);

		if ($message->attachment and isset($request->GET['delete-attachment'])) {
			$this->logger->logNotice("Deleting attachment for message with ID $message->id");
			$message->attachment->delete();
			$message->attachment = NULL;
		}

		$this->logger->logNotice("Marking message with ID $message->id as archived");

		$message->status = 'archived';
		$message->save();

		return $this->redirect($this->reverse('inbox'));
	}
}


class DeletionRequest extends \osmf\View\Transaction
{
	protected function getMessage($user, $id)
	{
		return models\Message::query()
			->where('recipient', 'eq', $user)
			->and('id', 'eq', $id)
			->one();
	}

	protected function render_POST($request, $args)
	{
		$message = $this->context->message = $this->getMessage(
			$request->user->getModel(), $args['id']
		);

		try {
			$token = models\Token::query()
				->where('message', 'eq', $message)
				->one();
		} catch (\osmf\Model\ObjectNotFound $e) {
			$this->logger->logInfo("Generating a new deletion token for message with ID $message->id");
			$token = new models\Token();
			$token->message = $message;
			$token->save();
		}

		$this->context->token = $token;
		$this->logger->logNotice("Deletion request with token $token->token for message with ID $message->id");

		return $this->renderResponse('messaging/delete-token.html');
	}
}


class Delete extends \osmf\View\Transaction
{
	protected function render_GET($request, $args)
	{
		$this->context->error = FALSE;
		$this->context->form = new forms\Delete();
		return $this->renderResponse('messaging/delete.html');
	}

	protected function render_POST($request, $args)
	{
		$form = new forms\Delete($request->POST);

		if ($form->isValid()) {
			$token = $form->cleaned_data['token'];
			$token = strtoupper($token);
			$token = str_replace('-', '', $token);

			try {
				$token = models\Token::query('admin')
					->where('token', 'eq', $token)
					->one();

				if ($form->cleaned_data['action'] == 'delete-message') {
					$message = $token->message;

					if ($message->attachment) {
						$this->logger->logNotice("Deleting attachments for message with ID $message->id");
						$message->attachment->delete();
					}
					$token->delete();
					$message->delete();
				} else {
					$token->delete();
				}

				$this->logger->logNotice("Message with ID $message->id correctly removed from the system");

				$this->message('The message and its attachments were correctly removed from the system!');
				return $this->redirect($this->reverse('delete-message'));
			} catch (\osmf\Model\ObjectNotFound $e) {
				$this->logger->logWarn("Invalid deletion token submitted");
			}
		}

		$this->context->error = TRUE;
		$this->context->form = $form;
		$this->setDbError();
		return $this->renderResponse('messaging/delete.html');

	}
}


class Attachment extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		$name = explode('-', $args['name']);
		$id = intval($name[0]);

		$message = models\Message::query()
			->where('recipient', 'eq', $request->user->getModel())
			->and('id', 'eq', $id)
			->one();

		$this->logger->logNotice("Downloading attachment for message with ID $message->id");

		// TODO: Raise 404 if message has no attachment, also check on disk if file exists
		return new \osmf\Http\Response\File($message->attachment);
	}
 }
