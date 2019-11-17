<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\FinalWorkBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FinalWork\FinalWorkBundle\Constant\FlashTypeConstant;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    WorkType
};
use FinalWork\FinalWorkBundle\Form\{
    WorkForm,
    UserForm,
    InsisLoginForm,
    InsisFilterForm
};
use FinalWork\FinalWorkBundle\Helper\InsisHTMLParserHelper;
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;
use OutOfBoundsException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse,
    Session\Session
};
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class InsisController extends BaseController
{
    /**
     * @param Request $request
     * @return RedirectResponse|Response
     *
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    public function loginAction(Request $request)
    {
        if ($this->checkPermission() === true) {
            return $this->redirectToRoute('insis_filter');
        }

        $form = $this->createForm(InsisLoginForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $login = $form->get('login')->getData();
            $password = $form->get('password')->getData();
            $status = InsisHTMLParserHelper::loginInsis($login, $password);

            if ($status) {
                return $this->redirectToRoute('insis_filter');
            }

            $this->addFlash(FlashTypeConstant::WARNING, $this->trans('finalwork.flash.form.create.warning'));

            return $this->render('@FinalWork/insis/login.html.twig', [
                'form' => $form->createView()
            ]);
        }

        return $this->render('@FinalWork/insis/login.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @return RedirectResponse
     */
    public function logoutAction(): RedirectResponse
    {
        $session = new Session;
        $session->remove('insis_cookies');

        return $this->redirectToRoute('insis_login');
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     *
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    public function filterAction(Request $request)
    {
        if ($this->checkPermission() === false) {
            return $this->redirect($this->generateUrl('insis_login'));
        }

        $selectFilter = InsisHTMLParserHelper::getSelectYearOption();
        $year = $selectFilter['year'];

        $type = [
            'all' => '0',
            'BP' => '1',
            'DP' => '2',
            'DisP' => '3'
        ];

        $form = $this->createForm(InsisFilterForm::class, null, [
            'year' => $year,
            'type' => $type
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $year = $form->get('year')->getData();
                $type = $form->get('type')->getData();

                $status = InsisHTMLParserHelper::selectFilterWork($year, $type);

                if ($status === true) {
                    return $this->redirectToRoute('insis_work_list');
                }

                $this->addFlash(FlashTypeConstant::WARNING, $this->trans('finalwork.flash.form.create.warning'));

                return $this->render('@FinalWork/insis/select.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            $this->addFlash(FlashTypeConstant::WARNING, $this->trans('finalwork.flash.form.create.warning'));
        }

        return $this->render('@FinalWork/insis/select.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @return Response
     *
     * @throws LogicException
     */
    public function listAction(): Response
    {
        if ($this->checkPermission() === false) {
            return $this->redirect($this->generateUrl('insis_login'));
        }

        $works = $this->getDoctrine()
            ->getRepository(Work::class)
            ->findAll();

        $insisWork = InsisHTMLParserHelper::getFilterWork(InsisHTMLParserHelper::createFilterWork($works), $this->get('doctrine.orm.entity_manager'));

        return $this->render('@FinalWork/insis/list.html.twig', [
            'works' => $insisWork,
        ]);
    }

    /**
     * @return bool|RedirectResponse
     */
    private function checkPermission()
    {
        $status = InsisHTMLParserHelper::checkPermission();

        return !($status === false);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function createFormUserInsisAction(Request $request): Response
    {
        $authorFirstName = $request->get('authorFirstName');
        $authorLastName = $request->get('authorLastName');
        $authorDegree = $request->get('authorDegree');

        $user = new User;
        $user->setDegreeBefore($authorDegree);
        $user->setFirstname($authorFirstName);
        $user->setLastname($authorLastName);

        $form = $this->createForm(UserForm::class, $user);

        if ($request->isXmlHttpRequest()) {
            return $this->render('@FinalWork/user/ajax/create.html.twig', [
                'reload' => false,
                'form' => $form->createView()
            ]);
        }

        return $this->render('@FinalWork/user/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws LogicException
     * @throws Exception
     */
    public function createFormWorkInsisAction(Request $request): Response
    {
        $user = $this->getUser();

        $authorFirstName = $request->get('authorFirstName');
        $authorLastName = $request->get('authorLastName');
        $workType = $request->get('workType');
        $workTitle = $request->get('workTitle');

        $today = new DateTime('now');

        $type = $this->getDoctrine()
            ->getRepository(WorkType::class)
            ->findOneBy([
                'shortcut' => $workType
            ]);

        $author = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'firstname' => $authorFirstName,
                'lastname' => $authorLastName
            ]);

        $work = new Work;
        $work->setTitle($workTitle);

        if ($type) {
            $work->setType($type);
        }

        if ($author) {
            $work->setAuthor($author);
        }

        $work->setDeadline($today);

        $workDeadLinesArrayResult = $this->getDoctrine()
            ->getRepository(Work::class)
            ->getWorkDeadlineBySupervisor($user)
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        $workDeadLines = new ArrayCollection;
        foreach ($workDeadLinesArrayResult as $workDeadLine) {
            $workDeadLines->add($workDeadLine['deadline']);
        }

        $workProgramDeadLinesArrayResult = $this->getDoctrine()
            ->getRepository(Work::class)
            ->getWorkProgramDeadlineBySupervisor($user)
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        $workProgramDeadLines = new ArrayCollection;
        foreach ($workProgramDeadLinesArrayResult as $workProgramDeadLine) {
            $workProgramDeadLines->add($workProgramDeadLine['deadlineProgram']);
        }

        $form = $this->createForm(WorkForm::class, $work, [
            'user' => $user
        ]);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            return $this->render('@FinalWork/work/ajax/work.html.twig', [
                'form' => $form->createView(),
                'workDeadlines' => $workDeadLines,
                'workProgramDeadlines' => $workProgramDeadLines,
                'buttonActionTitle' => $this->trans('finalwork.form.action.create')
            ]);
        }

        return $this->render('@FinalWork/work/work.html.twig', [
            'form' => $form->createView(),
            'workDeadlines' => $workDeadLines,
            'workProgramDeadlines' => $workProgramDeadLines,
            'buttonActionTitle' => $this->trans('finalwork.form.action.create')
        ]);
    }
}