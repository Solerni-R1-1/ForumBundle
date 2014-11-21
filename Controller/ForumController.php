<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Claroline\ForumBundle\Controller;

use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Category;
use Claroline\ForumBundle\Form\MessageType;
use Claroline\ForumBundle\Form\SubjectType;
use Claroline\ForumBundle\Form\CategoryType;
use Claroline\ForumBundle\Form\EditTitleType;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Session\Session;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * ForumController
 */
class ForumController extends Controller
{

    private function manageAno($nextUrl){
        if( $this->get('security.context')->getToken()->getUser() == 'anon.' ){
            $this->get('session')->set('nextUrl', $nextUrl)
            ;
            $route = $this->get('router')->generate('claro_security_login', array () );
            return $route;
        }
        return FALSE;
    }


    /**
     * @Route(
     *     "/{forum}/category",
     *     name="claro_forum_categories",
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @Template("ClarolineForumBundle::index.html.twig")
     *
     * @param Forum $forum
     * @param User $user
     */
    public function openAction(Forum $forum, User $user)
    {

        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_categories',
                    array ( 'forum' => $forum->getId() ) )
            );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }
       

       if ($this->checkAccess($forum, false)) {
        	$em = $this->getDoctrine()->getManager();
	        $categories = $em->getRepository('ClarolineForumBundle:Forum')->findCategories($forum);
	        $sc = $this->get('security.context');
	        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));
	
	        $moocSession = $em->getRepository('ClarolineCoreBundle:Mooc\\MoocSession')->getMoocSessionByForum($forum);
	        
	        return array(
	            'search' => null,
	            '_resource' => $forum,
	            'isModerator' => $isModerator,
	            'categories' => $categories,
	            //'hasSubscribed' => $this->get('claroline.manager.forum_manager')->hasSubscribed($user, $forum),
	        	'session' => $moocSession
	        );
        } else {
        	return $this->redirect($this->get('router')->generate('mooc_view', array('moocId' => $forum->getResourceNode()->getWorkspace()->getMooc()->getId(), 'moocName' => $forum->getResourceNode()->getWorkspace()->getMooc()->getTitle())));
        }
    }

    /**
     * @Route(
     *     "/category/{category}/subjects/page/{page}/max/{max}",
     *     name="claro_forum_subjects",
     *     defaults={"page"=1, "max"=20},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     *
     * @param Category $category
     * @param integer $page
     * @param integer $max
     */
    public function subjectsAction(Category $category, $page, $max, $user)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_subjects',
                    array ( 'category' => $category->getId(), 'page' => $page, '$max' => $max ) )
            );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $forum = $category->getForum();
        if ($this->checkAccess($forum, false)) {
        	$em = $this->getDoctrine()->getManager();
	        $pager = $this->get('claroline.manager.forum_manager')->getSubjectsPager($category, $page, $max);
	        $collection = new ResourceCollection(array($forum->getResourceNode()));
	        $sc = $this->get('security.context');
	        $canCreateSubject = $sc->isGranted('post', $collection);
	        $isModerator = $sc->isGranted('moderate', $collection);
	
	        $moocSession = $em->getRepository('ClarolineCoreBundle:Mooc\\MoocSession')->getMoocSessionByForum($forum);
	
	        return array(
	            'pager' => $pager,
	            '_resource' => $forum,
	            'canCreateSubject' => $canCreateSubject,
	            'isModerator' => $isModerator,
	            'category' => $category,
	            'max' => $max,
	        	'session' => $moocSession
	        );
        } else {
        	return $this->redirect($this->get('router')->generate('mooc_view', array('moocId' => $forum->getResourceNode()->getWorkspace()->getMooc()->getId(), 'moocName' => $forum->getResourceNode()->getWorkspace()->getMooc()->getTitle())));
        }
    }

    /**
     * @Route(
     *     "/form/subject/{category}",
     *     name="claro_forum_form_subject_creation"
     * )
     * @Template()
     *
     * @param Category $category
     */
    public function subjectFormAction(Category $category)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_form_subject_creation',
                    array ( 'category' => $category->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }


        $forum = $category->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $formSubject = $this->get('form.factory')->create(new SubjectType());

        return array(
            '_resource' => $forum,
            'form' => $formSubject->createView(),
            'category' => $category
        );
    }

    /**
     * @Route(
     *     "/form/category/{forum}",
     *     name="claro_forum_form_category_creation"
     * )
     * @Template()
     *
     * @param Forum $forum
     */
    public function categoryFormAction(Forum $forum)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_form_category_creation',
                    array ( 'forum' => $forum->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $formCategory = $this->get('form.factory')->create(new CategoryType());

        return array(
            '_resource' => $forum,
            'form' => $formCategory->createView()
        );
    }

    /**
     * @Route(
     *     "/category/create/{forum}",
     *     name="claro_forum_create_category"
     * )
     * @Template()
     * @param Forum $forum
     */
    public function createCategoryAction(Forum $forum)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_create_category',
                    array ( 'forum' => $forum->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $form = $this->get('form.factory')->create(new CategoryType(), new Category());
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $category = $form->getData();
            $this->get('claroline.manager.forum_manager')->createCategory($forum, $category->getName());
        }

        return new RedirectResponse(
            $this->generateUrl('claro_forum_categories', array('forum' => $forum->getId()))
        );
    }

    /**
     * The form submission is working but I had to do some weird things to make it works.
     *
     * @Route(
     *     "/subject/create/{category}",
     *     name="claro_forum_create_subject"
     * )
     * @Template("ClarolineForumBundle:Forum:subjectForm.html.twig")
     * @param Category $category
     */
    public function createSubjectAction(Category $category)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_create_subject',
                    array ( 'category' => $category->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $forum = $category->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $form = $this->get('form.factory')->create(new SubjectType(), new Subject);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $subject = $form->getData();
            $subject->setCreator($user);
            //instantiation of the new resources
            $subject->setCategory($category);
            $this->get('claroline.manager.forum_manager')->createSubject($subject);
            $dataMessage = $form->get('message')->getData();

            if ($dataMessage['content'] !== null) {
                $message = new Message();
                $message->setContent($dataMessage['content']);
                $message->setCreator($user);
                $message->setSubject($subject);
                $this->get('claroline.manager.forum_manager')->createMessage($message);

                return new RedirectResponse(
                    $this->generateUrl('claro_forum_subjects', array('category' => $category->getId()))
                );
            }
        }

        //throw new \Exception($form->getErrorsAsString());
        $form->get('message')->addError(
            new FormError($this->get('translator')->trans('field_content_required', array(), 'forum'))
        );

        return array(
            'form' => $form->createView(),
            'category' => $category,
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/subject/{subject}/messages/page/{page}/max/{max}/order/{order}",
     *     name="claro_forum_messages",
     *     defaults={"page"=1, "max"= 20, "order"="ASC"},
     *     options={"expose"=true}
     * )
     * @Route(
     *     "/subject/{subject}/messages/page/{page}/max/",
     *     name="claro_forum_messages_unordered",
     *     defaults={"page"=1, "max"= 20, "order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     *
     * @param Subject $subject
     * @param integer $page
     * @param integer $max
     */
    public function messagesAction(Subject $subject, $page, $max, $user, $order)
    {

        /* limit order values */
        $order = ($order === "DESC") ? "DESC" : "ASC";

        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_messages',
                    array ( 'subject' => $subject->getId(), 'page' => $page , 'max' => $max, 'order' => $order ) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }
        
        $forum = $subject->getCategory()->getForum();
        if ($this->checkAccess($forum, false)) {
        	$em = $this->getDoctrine()->getManager();
	        $isModerator = $this->get('security.context')->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));
            $firstMessage = $this->getDoctrine()->getRepository( 'ClarolineForumBundle:Message' )->findInitialBySubject($subject->getid());
	        $pager = $this->get('claroline.manager.forum_manager')->getMessagesPager($subject, $page, $max, $order);
	        $collection = new ResourceCollection(array($forum->getResourceNode()));
	        $canAnswer = $this->get('security.context')->isGranted('post', $collection);
	        $form = $this->get('form.factory')->create(new MessageType());
	        
	        $moocSession = $em->getRepository('ClarolineCoreBundle:Mooc\\MoocSession')->getMoocSessionByForum($forum);
			
	        return array(
	            'subject' => $subject,
                'firstMessage' => $firstMessage,
	            'pager' => $pager,
	            '_resource' => $forum,
	            'isModerator' => $isModerator,
	            'form' => $form->createView(),
	            'canAnswer' => $canAnswer,
	            'category' => $subject->getCategory(),
	            'max' => $max,
                'page' => $page,
                'order' => $order,
	        	'session' => $moocSession
	        
	        );
        } else {
        	return $this->redirect($this->get('router')->generate('mooc_view', array('moocId' => $forum->getResourceNode()->getWorkspace()->getMooc()->getId(), 'moocName' => $forum->getResourceNode()->getWorkspace()->getMooc()->getTitle())));
        }
    }

    /**
     * @Route(
     *     "/create/message/{subject}",
     *     name="claro_forum_create_message"
     * )
     *
     * @param Subject $subject
     */
    public function createMessageAction(Subject $subject)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_create_message',
                    array ( 'subject' => $subject->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->handleRequest($this->get('request'));
        $manager = $this->get('claroline.manager.forum_manager');
        $forum = $subject->getCategory()->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        if ($form->isValid()) {
            $message = $form->getData();
            $user = $this->get('security.context')->getToken()->getUser();
            $message->setCreator($user);
            $message->setSubject($subject);
            $manager->createMessage($message);
        }

        return new RedirectResponse(
            $this->generateUrl('claro_forum_messages', array('subject' => $subject->getId()))
        );
    }

    /**
     * @Route(
     *     "/edit/message/{message}/form",
     *     name="claro_forum_edit_message_form"
     * )
     * @Template()
     * @param Message $message
     */
    public function editMessageFormAction(Message $message)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_edit_message_form',
                    array ( 'message' => $message->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');
        $subject = $message->getSubject();
        $forum = $subject->getCategory()->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $message->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')->create(new MessageType(), $message);

        return array(
            'subject' => $subject,
            'form' => $form->createView(),
            'message' => $message,
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/edit/message/{message}",
     *     name="claro_forum_edit_message"
     * )
     *
     * @Template("ClarolineForumBundle:Forum:editMessageForm.html.twig")
     * @param Message $message
     */
    public function editMessageAction(Message $message)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_edit_message',
                    array ( 'message' => $message->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');
        $subject = $message->getSubject();
        $forum = $subject->getCategory()->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $message->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $oldContent = $message->getContent();
        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $newContent = $form->get('content')->getData();
            $this->get('claroline.manager.forum_manager')->editMessage($message, $oldContent, $newContent);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subject' => $subject->getId()))
            );
        }

        return array(
            'subject' => $subject,
            'form' => $form->createView(),
            'message' => $message,
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/edit/category/{category}/form",
     *     name="claro_forum_edit_category_form"
     * )
     * @Template()
     * @param Category $category
     */
    public function editCategoryFormAction(Category $category)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_edit_category_form',
                    array ( 'category' => $category->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');
        $forum = $category->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->container->get('form.factory')->create(new CategoryType, $category);
        $form->handleRequest($this->get('request'));

        return array(
            'category' => $category,
            'form' => $form->createView(),
            '_resource' => $category->getForum()
        );
    }

    /**
     * @Route(
     *     "/edit/category/{category}",
     *     name="claro_forum_edit_category"
     * )
     * @param Category $category
     */
    public function editCategoryAction(Category $category)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_edit_category',
                    array ( 'category' => $category->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');
        $forum = $category->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser()) {
            throw new AccessDeniedHttpException();
        }

        $oldName = $category->getName();
        $form = $this->container->get('form.factory')->create(new CategoryType, $category);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $newName = $form->get('name')->getData();
            $this->get('claroline.manager.forum_manager')->editCategory($category, $oldName, $newName);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_categories', array('forum' => $category->getForum()->getId()))
            );
        }
    }

    /**
     * @Route(
     *     "/delete/category/{category}",
     *     name="claro_forum_delete_category"
     * )
     *
     * @param Category $category
     */
    public function deleteCategory(Category $category)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_delete_category',
                    array ( 'category' => $category->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');
        $forum = $category->getForum();

        if ($sc->isGranted('moderate', new ResourceCollection(array($category->getForum()->getResourceNode())))) {

            $this->get('claroline.manager.forum_manager')->deleteCategory($category);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_categories', array('forum' => $forum->getId()))
            );
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @Route(
     *     "/{forum}/search/{search}/page/{page}",
     *     name="claro_forum_search",
     *     defaults={"page"=1, "search"= ""},
     *     options={"expose"=true}
     * )
     * @Template("ClarolineForumBundle::searchResults.html.twig")
     * @param Forum $forum
     * @param integer $page
     * @param string $search
     */
    public function searchAction(Forum $forum, $page, $search)
    {
        $pager = $this->get('claroline.manager.forum_manager')->searchPager($forum, $search, $page);

        return array('pager' => $pager, '_resource' => $forum, 'search' => $search, 'page' => $page);
    }

     /**
     * @Route(
     *     "/edit/subject/{subjectId}/form",
     *     name="claro_forum_edit_subject_form"
     * )
     * @ParamConverter(
     *      "subject",
     *      class="ClarolineForumBundle:Subject",
     *      options={"id" = "subjectId", "strictId" = true}
     * )
     * @Template()
     * @param Subject $subject
     */
    public function editSubjectFormAction(Subject $subject)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_edit_subject_form',
                    array ( 'subjectId' => $subject->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($subject->getCategory()->getForum()->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $subject->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->container->get('form.factory')->create(new EditTitleType(), $subject);

        return array(
            'form' => $form->createView(),
            'subject' => $subject,
            'forumId' => $subject->getCategory()->getForum()->getId(),
            '_resource' => $subject->getCategory()->getForum()
        );
    }

    /**
     * @Route(
     *     "/edit/subject/{subjectId}/submit",
     *     name="claro_forum_edit_subject"
     * )
     * @ParamConverter(
     *      "subject",
     *      class="ClarolineForumBundle:Subject",
     *      options={"id" = "subjectId", "strictId" = true}
     * )
     * @Template("ClarolineForumBundle:Forum:editSubjectForm.html.twig")
     * @param Subject $subject
     */
    public function editSubjectAction(Subject $subject)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_edit_subject',
                    array ( 'subjectId' => $subject->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');
        $isModerator = $sc->isGranted(
            'moderate', new ResourceCollection(array($subject->getCategory()->getForum()->getResourceNode()))
        );

        if (!$isModerator && $sc->getToken()->getUser() !== $subject->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $oldTitle = $subject->getTitle();
        $form = $this->container->get('form.factory')->create(new EditTitleType(), $subject);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $newTitle = $form->get('title')->getData();
            $this->get('claroline.manager.forum_manager')->editSubject($subject, $oldTitle, $newTitle);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_subjects', array('category' => $subject->getCategory()->getId()))
            );
        }

        return array(
            'form' => $form->createView(),
            'subjectId' => $subject->getId(),
            'forumId' => $subject->getCategory()->getForum()->getId(),
            '_resource' => $subject->getCategory()->getForum()
        );
    }

    /**
     * @Route(
     *     "/delete/message/{message}",
     *     name="claro_forum_delete_message"
     * )
     *
     * @param \Claroline\ForumBundle\Entity\Message $message
     */
    public function deleteMessageAction(Message $message)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_delete_message',
                    array ( 'message' => $message->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');

        if ($sc->isGranted('moderate', new ResourceCollection(array($message->getSubject()->getCategory()->getForum()->getResourceNode())))) {
            $this->get('claroline.manager.forum_manager')->deleteMessage($message);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subject' => $message->getSubject()->getId()))
            );
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @Route(
     *     "/subscribe/forum/{forum}",
     *     name="claro_forum_subscribe"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param Forum $forum
     * @param User $user
     */
    public function subscribeAction(Forum $forum, User $user)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_subscribe',
                    array ( 'forum' => $forum->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $manager = $this->get('claroline.manager.forum_manager');
        $manager->subscribe($forum, $user);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_categories', array('forum' => $forum->getId()))
        );
    }

    /**
     * @Route(
     *     "/unsubscribe/forum/{forum}",
     *     name="claro_forum_unsubscribe"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param Forum $forum
     * @param User $user
     */
    public function unsubscribeAction(Forum $forum, User $user)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_unsubscribe',
                    array ( 'forum' => $forum->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $manager = $this->get('claroline.manager.forum_manager');
        $manager->unsubscribe($forum, $user);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_categories', array('forum' => $forum->getId()))
        );
    }

    /**
     * @Route(
     *     "/delete/subject/{subject}",
     *     name="claro_forum_delete_subject"
     * )
     *
     * @param Subject $subject
     */
    public function deleteSubjectAction(Subject $subject)
    {

        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_forum_delete_subject',
                    array ( 'subject' => $subject->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $sc = $this->get('security.context');

        if ($sc->isGranted('moderate', new ResourceCollection(array($subject->getCategory()->getForum()->getResourceNode())))) {

            $this->get('claroline.manager.forum_manager')->deleteSubject($subject);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_subjects', array('category' => $subject->getCategory()->getId()))
            );
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     * @throws AccessDeniedHttpException
     */
    private function checkAccess(Forum $forum, $throwException = true)
    {
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('OPEN', $collection)) {
        	if ($throwException) {
            	throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        	} else {
        		return false;
        	}
        }
        
        return true;
    }

    protected function dispatch($event)
    {
        $this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }

    /**
     * @EXT\Route(
     *     "/forums/workspace/{workspaceId}",
     *     name="claro_workspace_forums",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Renders last messages from the forums' workspace
     *
     * @param AbstractWorkspace $workspace
     * @param boolean $isMini
     */
    public function forumsWorkspaceWidgetAction(AbstractWorkspace $workspace, $isMini = false)
    {
        $sc = $this->get('security.context');
        $user = $sc->getToken()->getUser();
        $utils = $this->get('claroline.security.utilities');
        $token = $sc->getToken($user);
        $roles = $utils->getRoles($token);


        $moocSession = $this->getDoctrine()
	        ->getRepository( 'ClarolineCoreBundle:Mooc\\MoocSession' )
	        ->guessMoocSession($workspace, $user);

        $workspaces = array();
        $workspaces[] = $workspace;
        $em = $this->getDoctrine()->getManager();
        // Get the 3 last messages from all forums from the workspace
        $messages = $em->getRepository('ClarolineForumBundle:Message')
                ->findNLastBySession($moocSession, $roles,3);

        
        $forum = $em->getRepository('ClarolineForumBundle:Forum')
        		->getForumAssociatedToSession($moocSession->getId());

        return array('widgetType' => 'workspace', 'messages' => $messages, 'isMini' => $isMini, 'forum' => $forum);
    }
    
    /**
     * @EXT\Route(
     *     "/forums/{nodeId}",
     *     name="claro_forum",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "node",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "nodeId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Renders last messages from the forum
     *
     * @param ResourceNode $node
     * @param boolean $isMini
     */
    public function forumsWidgetAction(ResourceNode $node, $isMini = false)
    {
    	$sc = $this->get('security.context');
    	$user = $sc->getToken()->getUser();
    	$utils = $this->get('claroline.security.utilities');
    	$token = $sc->getToken($user);
    	$roles = $utils->getRoles($token);
    	$em = $this->getDoctrine()->getManager();

    	$forum = $em->getRepository('ClarolineForumBundle:Forum')
    			->getForumFromResourceNode($node);
        	
    	// Get the 3 last messages from all forums from the workspace
    	$messages = $em->getRepository('ClarolineForumBundle:Message')
    			->findNLast($forum, $roles,3);
    
    	return array('widgetType' => 'workspace', 'messages' => $messages, 'isMini' => $isMini, 'forum' => $forum);
    }
    
    /**
     * @EXT\Route(
     *     "/forums",
     *     name="claro_desktop_forums",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders last messages from the forums' workspaces
     */
    public function forumsDesktopWidgetAction()
    {
        $sc = $this->get('security.context');
        $user = $sc->getToken()->getUser();
        $utils = $this->get('claroline.security.utilities');
        $token = $sc->getToken();
        $roles = $utils->getRoles($token);

        // Get user workspaces
        $manager = $this->get('claroline.manager.workspace_manager');
        $workspaces = $manager->getWorkspacesByUser($user);
        $em = $this->getDoctrine()->getManager();

        // Get the 3 last messages from all forums from the workspaces
        $messages = $em->getRepository('ClarolineForumBundle:Message')
                ->findNLastByForum($workspaces, $roles,3);

        return array('widgetType' => 'desktop', 'messages' => $messages);
    }

    /**
     * @EXT\Route(
     *     "/subject/{subject}/move/form",
     *     name="claro_subject_move_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Method("GET")
     * @EXT\Template()
     * @param Subject $subject
     */
    public function moveSubjectFormAction(Subject $subject, User $user)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_subject_move_form',
                    array ( 'subject' => $subject->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $category = $subject->getCategory();
        $forum = $category->getForum();
        $this->checkAccess($forum);
        $categories = $forum->getCategories();

        return array(
            '_resource' => $forum,
            'categories' => $categories,
            'category' => $category,
            'subject' => $subject
        );
    }

    /**
     * @EXT\Route(
     *     "/message/{message}/move/form/page/{page}",
     *     name="claro_message_move_form",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Method("GET")
     * @EXT\Template()
     * @param Message $message
     * @param integer $page
     */
    public function moveMessageFormAction(Message $message, $page, User $user)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_message_move_form',
                    array ( 'message' => $message->getId(), 'page' => $page) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $subject = $message->getSubject();
        $category = $subject->getCategory();
        $forum = $category->getForum();
        $this->checkAccess($forum);
        $pager = $this->get('claroline.manager.forum_manager')->getSubjectsPager($category, $page);

        return array(
            '_resource' => $forum,
            'category' => $category,
            'subject' => $subject,
            'pager' => $pager,
            'message' => $message
        );
    }

    /**
     * @EXT\Route(
     *     "/message/{message}/move/{newSubject}",
     *     name="claro_message_move",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Method("GET")
     *
     * @param Message $message
     * @param Subject $newSubject
     */
    public function moveMessageAction(Message $message, Subject $newSubject, User $user)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_message_move_form',
                    array ( 'message' => $message->getId(), 'newSubject' => $newSubject) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $forum = $newSubject->getCategory()->getForum();
        $this->checkAccess($forum);
        $manager = $this->get('claroline.manager.forum_manager');
        $manager->moveMessage($message, $newSubject);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_subjects', array('category' => $newSubject->getCategory()->getId()))
        );
    }

    /**
     * @EXT\Route(
     *     "/subject/{subject}/move/{newCategory}",
     *     name="claro_subject_move",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Method("GET")
     *
     * @param Subject $subject
     * @param Category $newCategory
     */
    public function moveSubjectAction(Subject $subject, Category $newCategory, User $user)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_subject_move',
                    array ( 'subject' => $subject->getId(), 'newCategory' => $newCategory) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $forum = $newCategory->getForum();
        $this->checkAccess($forum);
        $manager = $this->get('claroline.manager.forum_manager');
        $manager->moveSubject($subject, $newCategory);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_categories', array('forum' => $forum->getId()))
        );
    }

    /**
     * @EXT\Route(
     *     "/stick/subject/{subject}",
     *     name="claro_subject_stick",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Method("GET")
     *
     * @param Subject $subject
     */
    public function stickSubjectAction(Subject $subject, User $user)
    {

        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_subject_stick',
                    array ( 'subject' => $subject->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $forum = $subject->getCategory()->getForum();
        $this->checkAccess($forum);
        $manager = $this->get('claroline.manager.forum_manager');
        $manager->stickSubject($subject);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_subjects', array('category' => $subject->getCategory()->getId()))
        );
    }

    /**
     * @EXT\Route(
     *     "/unstick/subject/{subject}",
     *     name="claro_subject_unstick",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Method("GET")
     *
     * @param Subject $subject
     */
    public function unstickSubjectAction(Subject $subject, User $user)
    {
        $redirect = $this->manageAno(
            $this->get('router')->generate('claro_subject_unstick',
                    array ( 'subject' => $subject->getId()) ) );
        if (FALSE !== $redirect) {
            return new RedirectResponse($redirect);
        }

        $forum = $subject->getCategory()->getForum();
        $this->checkAccess($forum);
        $manager = $this->get('claroline.manager.forum_manager');
        $manager->unstickSubject($subject);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_subjects', array('category' => $subject->getCategory()->getId()))
        );
    }
}
