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

namespace FinalWork\FinalWorkBundle\Helper;

use Doctrine\ORM\EntityManager;
use FinalWork\FinalWorkBundle\Entity\Work;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\Session;
use GuzzleHttp\Client as GuzzleHttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Goutte;

class InsisHTMLParserHelper
{
    /**
     * @var string
     */
    public const insisPage = 'https://insis.vse.cz/';

    /**
     * @var string
     */
    public const loginPage = 'https://insis.vse.cz/auth';

    /**
     * @var string
     */
    public const fotoPage = 'https://insis.vse.cz/auth/lide/foto.pl';

    /**
     * @var string
     */
    public const teacherPage = 'https://insis.vse.cz/auth/lide/clovek.pl?id=;zalozka=13;lang=cz';

    /**
     * @param $login
     * @param $password
     * @return bool
     */
    public static function loginInsis($login, $password): bool
    {
        $client = new Goutte\Client();
        $guzzleClient = new GuzzleHttpClient(
            [
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ],
                'cookies' => true
            ]
        );
        $client->setClient($guzzleClient);

        $loginCrawler = $client->request('GET', self::loginPage);
        $form = $loginCrawler->selectButton('login')->form();
        $form['credential_0'] = $login;
        $form['credential_1'] = $password;

        $client->submit($form);
        self::setCookies($client->getCookieJar()->all());

        if (self::checkPermission()) {
            return true;
        }

        return false;
    }

    /**
     * Metoda pro získání informací o filtrovaných závěrečných pracích z InSIS
     *
     * @param $year
     * @param $type
     * @return bool
     */
    public static function selectFilterWork($year, $type): bool
    {
        $client = new Goutte\Client();
        $guzzleClient = new GuzzleHttpClient(
            [
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ],
                'cookies' => true
            ]
        );
        $client->setClient($guzzleClient);
        $client->getCookieJar()->updateFromSetCookie(self::getCookies());

        $crawler = $client->request('GET', self::teacherPage);
        $form = $crawler->selectButton('omezit_prace')->form();
        $form['filtr_rokprace'] = $year;
        $form['filtr_typprace'] = $type;

        $client->submit($form);
        $response = $client->getResponse();

        return $response->getStatus() === 200;
    }

    /**
     * Metoda pro získání informací o filtrovaných závěrečných pracích z InSIS
     * @param array $filterWorks
     * @param EntityManager $entityManager
     * @return array
     */
    public static function getFilterWork(array $filterWorks = [], EntityManager $entityManager): array
    {
        $client = new Goutte\Client();
        $guzzleClient = new GuzzleHttpClient(
            [
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ],
                'cookies' => true
            ]
        );
        $client->setClient($guzzleClient);
        $client->getCookieJar()->updateFromSetCookie(self::getCookies());
        $crawler = $client->request('GET', self::teacherPage);

        $rows = $crawler->filter('#tmtab_1 > tbody > tr');
        $isisWorks = [
            'yes' => [],
            'no' => []
        ];

        foreach ($rows as $key => $row) {
            $row = new Crawler($row);

            $workType = $row->filter('td')->eq(2)->filter('small')->text();
            $authorFullName = $row->filter('td')->eq(3)->filter('small > a')->text();
            $authorDetailLink = $row->filter('td')->eq(3)->filter('small > a')->attr('href');
            $workName = $row->filter('td')->eq(3)->filter('small')->text();
            $workDate = $row->filter('td')->eq(4)->filter('small')->text();

            $workName = \str_replace($authorFullName, '~', $workName);
            $pos = \strpos($workName, '~');
            $workTitle = substr($workName, $pos + 1);

            $authorInsisId = null;
            if (preg_match('/id=(\d+)/', $authorDetailLink, $matches)) {
                $authorInsisId = $matches[1];
            }

            $authorDegree = null;
            $authorOriginalFullName = $authorFullName;
            foreach (self::getAllAcademicDegree() as $degree) {
                if (\strpos($authorFullName, $degree) !== false) {
                    $authorDegree .= ' ' . $degree;
                    $authorDegree = \trim($authorDegree);
                    $authorFullName = \trim(\str_replace($degree, '', $authorFullName));
                }
            }

            $authorNameSurname = \explode(' ', $authorFullName, 2);
            $authorFirstName = $authorNameSurname[0] ?? '';
            $authorLastName = $authorNameSurname[1] ?? '';

            $authorExist = $entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'firstname' => $authorFirstName,
                    'lastname' => $authorLastName
                ]);

            $insisWork = [];
            $insisWork['authorFullName'] = $authorOriginalFullName;
            $insisWork['authorFirstName'] = $authorFirstName;
            $insisWork['authorLastName'] = $authorLastName;
            $insisWork['authorDegree'] = $authorDegree;
            $insisWork['authorImage'] = self::fotoPage . '?id=' . $authorInsisId;
            $insisWork['workType'] = $workType;
            $insisWork['workTitle'] = $workTitle;
            $insisWork['workDate'] = $workDate;
            $insisWork['authorISISID'] = $authorInsisId;
            $insisWork['authorExist'] = $authorExist ? true : false;

            $workTitle = \preg_replace('/\xc2\xa0/', ' ', $workTitle);
            $checkerString = \trim($authorFirstName . '_' . $authorLastName . '_' . \trim(\preg_replace('/\s+/', '_', $workTitle)));

            if (\in_array($checkerString, $filterWorks, true)) {
                $isisWorks['yes'][] = $insisWork;
            } else {
                $isisWorks['no'][] = $insisWork;
            }
        }

        return $isisWorks;
    }

    /**
     * @param $user_id
     */
    public static function getUserImage($user_id): void
    {
        $client = new Goutte\Client();
        $guzzleClient = new GuzzleHttpClient(
            [
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ],
                'cookies' => true
            ]
        );
        $client->setClient($guzzleClient);
        $client->getCookieJar()->updateFromSetCookie(self::getCookies());
        $client->request('GET', self::fotoPage . '?id=' . $user_id);
    }

    /**
     * @param $detail_link
     * @return string
     */
    public static function getWorkInformation($detail_link): string
    {
        $client = new Goutte\Client();
        $guzzleClient = new GuzzleHttpClient(
            [
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ],
                'cookies' => true
            ]
        );

        $client->setClient($guzzleClient);
        $client->getCookieJar()->updateFromSetCookie(self::getCookies());

        $linkDetail = \str_replace('..', '', $detail_link);
        $linkDetail = self::loginPage . $linkDetail;

        $crawlerDetail = $client->request('GET', $linkDetail);
        $imgLink = $crawlerDetail->filter('a[href*="zobraz_zp"]');

        $linkDetail = \str_replace('..', '', $imgLink->attr('href'));
        $linkDetail = self::loginPage . $linkDetail;

        $crawlerTable = $client->request('GET', $linkDetail);
        $htmlTable = $crawlerTable->filter('table[class="velky_ramecek"]');

        return $htmlTable->html();
    }

    /**
     * Metoda pro určení, zdali má uživatel práva na zobrazení příslušné stránky systému InSIS
     *
     * @return bool
     */
    public static function checkPermission(): bool
    {
        if (self::getCookies()) {
            $client = new Goutte\Client();
            $guzzleClient = new GuzzleHttpClient(
                [
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false
                    ],
                    'cookies' => true
                ]
            );

            $client->setClient($guzzleClient);
            $client->getCookieJar()->updateFromSetCookie(self::getCookies());
            $client->request('GET', self::teacherPage);
            $response = $client->getResponse();

            return $response->getStatus() === 200;
        }

        return false;
    }

    /**
     * Metoda pro získání filtru pro závěrečné práce
     *
     * @param array
     * @return array
     */
    public static function createFilterWork($works): array
    {
        $uniqueWorkIdArray = [];

        /** @var Work $work */
        foreach ($works as $work) {

            $authorFullName = $work->getAuthor()->getFirstname() . '_' . $work->getAuthor()->getLastname();
            $workTitle = preg_replace('/\xc2\xa0/', ' ', $work->getTitle());
            $workTitle = trim(preg_replace('/\s+/', '_', $workTitle));
            $workType = $work->getType()->getShortcut();

//            $uniqueWorkIdArray[] = trim($authorFullName . '_' . $workTitle . '_' . $workType);
            $uniqueWorkIdArray[] = trim($authorFullName . '_' . $workTitle);
        }

        return $uniqueWorkIdArray;
    }

    /**
     * Metoda pro získání emailu konkrétního uživatele z InSIS
     *
     * @param int
     * @param string
     * @param string
     * @return string
     */
    public static function getAuthorEmail($authorISISId, $myISISUsername, $myISISPassword): string
    {
        $studentDetailCrawler = InsisHTMLParserHelper::getCrawlerOverLogin('https://isis.vse.cz/auth/lide/clovek.pl?id=' . $authorISISId, $myISISUsername, $myISISPassword);
        return \str_replace(' [at] ', '@', \trim($studentDetailCrawler->filter('table')
            ->eq(0)
            ->filter('table')
            ->eq(1)
            ->filter('tr')
            ->eq(2)
            ->filter('td')
            ->eq(0)
            ->filter('small > a')
            ->text()));
    }

    /**
     * Metoda pro získání xname uživatele z jeho emailu
     *
     * @param string
     * @return string
     */
    public static function getAuthorXname($email): string
    {
        return \explode('@', $email)[0];
    }

    /**
     * @return mixed|null
     */
    public static function getCookies()
    {
        $session = new Session();

        if ($session->get('insis_cookies_end') <= \time()) {
            $session->remove('insis_cookies');
            $session->remove('insis_cookies_end');
        }

        if ($session->get('insis_cookies')) {
            return \unserialize($session->get('insis_cookies'));
        }

        return null;
    }

    /**
     * @param mixed $cookies
     */
    public static function setCookies($cookies): void
    {
        $session = new Session();
        $session->set('insis_cookies', \serialize($cookies));
        $session->set('insis_cookies_end', \strtotime('now +1 hour'));
    }

    /**
     * @return array
     */
    public static function getSelectYearOption(): array
    {
        $client = new Goutte\Client();
        $guzzleClient = new GuzzleHttpClient(
            [
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ],
                'cookies' => true
            ]
        );
        $client->setClient($guzzleClient);
        $client->getCookieJar()->updateFromSetCookie(self::getCookies());


        $selectFilter = [
            'year' => [],
            'type' => [],
        ];

        $crawler = $client->request('GET', self::teacherPage);
        $form = $crawler->selectButton('omezit_prace')->form();

        $years = $form['filtr_rokprace']->availableOptionValues();

        $years[0] = 'all';
        $years = \array_flip($years);
        foreach ($years as $key => $year) {
            if ($key === 'all') {
                continue;
            }

            $years[$key] = $key;
        }

        $selectFilter['year'] = $years;

        return $selectFilter;
    }

    /**
     * @return array
     */
    private static function getAllAcademicDegree(): array
    {
        return [
            'Bc.',
            'BcA.',
            'Ing.',
            'Ing. arch.',
            'JUDr.',
            'MDDr.',
            'MgA.',
            'Mgr.',
            'MSDr.',
            'MUDr.',
            'MVDr.',
            'PaedDr.',
            'PharmDr.',
            'PhDr.',
            'PhMr.',
            'RCDr.',
            'RNDr.',
            'RSDr.',
            'RTDr.',
            'ThDr.',
            'ThLic.',
            'ThMgr.',
            'Ph.D.'
        ];
    }
}