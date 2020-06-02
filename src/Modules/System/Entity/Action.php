<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\System\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\Security\Entity\Role;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Action
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ActionRepository")
 * @ORM\Table(name="Action",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="module_name", columns={"name", "module"}),
 *     @ORM\UniqueConstraint(name="entry_route",columns={"entry_route"})},
 *     indexes={@ORM\Index(name="module", columns={"module"})})
 * @UniqueEntity(fields={"name","module"})
 * @UniqueEntity(fields={"entryRoute"})
 */
class Action extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Module", inversedBy="actions")
     * @ORM\JoinColumn(name="module",referencedColumnName="id", nullable=false)
     */
    private $module;

    /**
     * @var string|null
     * @ORM\Column(length=50, options={"comment": "The action name should be unqiue to the module that it is related to"})
     */
    private $name;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", columnDefinition="INT(2)")
     */
    private $precedence;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     */
    private $category;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $description;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array", name="route_List")
     */
    private $routeList;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $entryRoute;

    /**
     * @var string
     * @ORM\Column(length=1,options={"default": "Y"})
     */
    private $entrySidebar = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1,options={"default": "Y"})
     */
    private $menuShow = 'Y';

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $role;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param string|null $id
     * @return Action
     */
    public function setId(?string $id): Action
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Module|null
     */
    public function getModule(): ?Module
    {
        return $this->module;
    }

    /**
     * @param Module|null $module
     * @return Action
     */
    public function setModule(?Module $module): Action
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * getDisplayName
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        if (null === $this->getName())
            return null;
        $name = explode('_', $this->name);
        return $name[0];
    }

    /**
     * @param string|null $name
     * @return Action
     */
    public function setName(?string $name): Action
    {
        $this->name = mb_substr($name, 0, 50);
        return $this;
    }

    /**
     * getTranslatedName
     * @return string|null
     */
    public function getTranslatedName(): ?string
    {
        if (null === $this->getName())
            return null;
        $name = explode('_', $this->getName());
        $domain = $this->getModule() ? str_replace(' ', '', $this->getModule()->getName()) : 'messages';
        return TranslationHelper::translate($name[0], [], $domain);
    }

    /**
     * @return int|null
     */
    public function getPrecedence(): ?int
    {
        return $this->precedence;
    }

    /**
     * @param int|null $precedence
     * @return Action
     */
    public function setPrecedence(?int $precedence): Action
    {
        $this->precedence = $precedence;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     * @return Action
     */
    public function setCategory(?string $category): Action
    {
        $this->category = mb_substr($category, 0, 20);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Action
     */
    public function setDescription(?string $description): Action
    {
        $this->description = mb_substr($description, 0, 255);
        return $this;
    }

    /**
     * @return array
     */
    public function getRouteList(): array
    {
        if (is_string($this->routeList))
            $this->routeList = [$this->routeList];
        return $this->routeList ?: [];
    }

    /**
     * RouteList.
     *
     * @param array|null $RouteList
     * @return Action
     */
    public function setRouteList(?array $RouteList): Action
    {
        $this->routeList = $RouteList;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getentryRoute(): ?string
    {
        return $this->entryRoute;
    }

    /**
     * setentryRoute
     * @param string|null $entryRoute
     * @return Action
     */
    public function setentryRoute(?string $entryRoute): Action
    {
        $this->entryRoute = mb_substr($entryRoute, 0, 255);
        return $this;
    }

    /**
     * isEntrySidebar
     * @return bool
     */
    public function isEntrySidebar(): bool
    {
        return $this->getEntrySidebar() === 'Y';
    }

    /**
     * @return string
     */
    public function getEntrySidebar(): string
    {
        return self::checkBoolean($this->entrySidebar);
    }

    /**
     * @param string $entrySidebar
     * @return Action
     */
    public function setEntrySidebar(string $entrySidebar): Action
    {
        $this->entrySidebar = self::checkBoolean($entrySidebar);
        return $this;
    }

    /**
     * @return string
     */
    public function getMenuShow(): string
    {
        return $this->menuShow;
    }

    /**
     * @param string $menuShow
     * @return Action
     */
    public function setMenuShow(string $menuShow): Action
    {
        $this->menuShow = in_array($menuShow, self::getBooleanList()) ? $menuShow : 'Y';
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRole(): ?array
    {
        return $this->role;
    }

    /**
     * Role.
     *
     * @param array|null $role
     * @return Action
     */
    public function setRole(?array $role): Action
    {
        $this->role = $role;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        if ($name === 'module_menu') {
            return [
                'category' => $this->getCategory(),
                'moduleName' => $this->getModule()->getName(),
                'actionName' => $this->getName(),
                'type' => $this->getModule()->getType(),
                'precedence' => $this->getPrecedence(),
                'moduleEntry' => $this->getModule()->getEntryRoute(),
                'entryRoute' => $this->getentryRoute(),
                'routeList' => $this->getRouteList(),
                'name' => $this->getDisplayName(),
            ];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'translatedName' => $this->getTranslatedName(),
            'precedence' => $this->precedence,
            'category' => $this->category,
            'routeList' => $this->routeList,
            'entryRoute' => $this->entryRoute,
            'entrySidebar' => $this->entrySidebar,
            'menuShow' => $this->menuShow,
            'module' => $this->getModule() ? $this->getModule()->getId() : null,
        ];
    }

    /**
     * getRouteName
     * @param String $module
     * @param string $action
     * @return string
     */
    public static function getRouteName(String $module, string $action)
    {
        return strtolower(str_replace(' ', '_',  $module). '__' . $action);
    }

    /**
     * create
     * @return string
     */
    public function create(): array
    {
        return ["CREATE TABLE IF NOT EXISTS `__prefix__Action` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(50) NOT NULL COMMENT 'The action name should be unique to the module that it is related to',
                    `precedence` int(2) DEFAULT NULL,
                    `category` CHAR(20) NOT NULL,
                    `description` CHAR(191) NOT NULL,
                    `route_list` longtext NOT NULL COMMENT '(DC2Type:simple_array)',
                    `entry_route` CHAR(191) NOT NULL,
                    `entry_sidebar` CHAR(1) NOT NULL DEFAULT 'Y',
                    `menu_show` CHAR(1) NOT NULL DEFAULT 'Y',
                    `role` CHAR(32) DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                    `module` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `moduleName` (`name`,`module`),
                    KEY `module` (`module`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Action`
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);";
    }

    public function coreData(): array
    {
        return Yaml::parse("
-
  name: 'Personal Preferences'
  precedence: 0
  category: 'People Management'
  description: 'Allows you to set your own preferences for background, theme, et.al. Change your password.'
  routeList: ['preferences','person_reset_password']
  entryRoute: 'preferences'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_CURRENT_USER']
-
  name: 'System Settings'
  precedence: 0
  category: 'Settings'
  description: 'Allows administrators to configure the system display settings.'
  routeList: ['system_settings']
  entryRoute: 'system_settings'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SYSTEM_ADMIN']
-
  name: 'Manage People'
  precedence: 0
  category: 'People Management'
  description: 'Allows management of all people database records.'
  routeList: ['people_list','people_content_loader','person_add','person_edit','person_delete.people_list_filter']
  entryRoute: 'people_list'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'Third Party Settings'
  precedence: 0
  category: 'Settings'
  description: 'Allows administrators to configure and make use of third party services.'
  routeList: ['third_party_settings','test_email']
  entryRoute: 'third_party_settings'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SYSTEM_ADMIN']
-
  name: 'System Check'
  precedence: 0
  category: 'Extend & Update'
  description: 'Check system versions and extensions installed.'
  routeList: ['system_check']
  entryRoute: 'system_check'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SYSTEM_ADMIN']
-
  name: 'String Replacement'
  precedence: 0
  category: 'Settings'
  description: 'Allows for interface strings to be replaced with custom values.'
  routeList: ['string_manage','string_edit','string_add','string_delete']
  entryRoute: 'string_manage'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'Notification Settings'
  precedence: 0
  category: 'Settings'
  description: 'Manage settings for system notifications.'
  routeList: ['notification_events','notification_event_edit','notification_listener_delete']
  entryRoute: 'notification_events'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SYSTEM_ADMIN']
-
  name: 'Display Settings'
  precedence: 0
  category: 'Settings'
  description: 'Allows system administrators to configure the system display settings.'
  routeList: ['display_settings']
  entryRoute: 'display_settings'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SYSTEM_ADMIN']
-
  name: 'Import Personal Photos'
  precedence: 0
  category: 'People Management'
  description: 'Upload photos direct to a users record.'
  routeList: ['personal_photo_import','personal_photo_upload_api','personal_photo_remove_api']
  entryRoute: 'personal_photo_import'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SUPPORT']
-
  name: 'Manage Families'
  precedence: 0
  category: 'People Management'
  description: ''
  routeList: ['family_list','family_content_loader','family_add','family_edit','family_relationships','family_adult_edit','family_adult_sort','family_adult_add','family_adult_remove','family_student_edit','family_student_add','family_student_remove']
  entryRoute: 'family_list'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'People Settings'
  precedence: 0
  category: 'Settings'
  description: 'Configure settings relating to people management.'
  routeList: ['people_settings']
  entryRoute: 'people_settings'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'Student Settings'
  precedence: 0
  category: 'Settings'
  description: 'Manage settings for the Student module'
  routeList: ['student_settings','student_note_category_edit','student_note_category_add','student_note_category_delete']
  entryRoute: 'student_settings'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'View Student Profile_full'
  precedence: 3
  category: 'Profiles'
  description: 'View full profile of any student in the school.'
  routeList: ['student_view']
  entryRoute: 'student_view'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_TEACHER,ROLE_SUPPORT']
-
  name: 'View Staff Profile_full'
  precedence: 2
  category: 'Profiles'
  description: 'View full profile of any staff member in the school.'
  routeList: ['staff_view']
  entryRoute: 'staff_view'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SUPPORT']
-
  name: 'Updater Settings'
  precedence: 0
  category: 'Settings'
  description: 'Configure options for the Data Updater module'
  routeList: ['updater_settings']
  entryRoute: 'updater_settings'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'Manage Addresses'
  precedence: 0
  category: 'People Management'
  description: 'Manage addresses used for contacts in the database.'
  routeList: ['address_list','address_edit_popup','address_add','address_add_popup','address_delete','address_list_refresh']
  entryRoute: 'address_list'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SUPPORT']
-
  name: 'Manage Phones'
  precedence: 0
  category: 'People Management'
  description: 'Manage phones used for contacts in the database.'
  routeList: ['phone_list','phone_edit_popup','phone_add','phone_add_popup','phone_delete','phone_refresh']
  entryRoute: 'phone_list'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SUPPORT']
-
  name: 'Manage Localities'
  precedence: 0
  category: 'People Management'
  description: 'Manage localities used for contacts in the database.'
  routeList: ['locality_list','locality_edit_popup','locality_add','locality_add_popup','locality_delete','locality_list_refresh']
  entryRoute: 'locality_list'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SUPPORT']
-
  name: 'Manage Custom Fields'
  precedence: 0
  category: 'People Management'
  description: 'Manage custom fields for personal.'
  routeList: ['custom_field_list','custom_field_edit','custom_field_add','custom_field_delete','custom_field_filter_save']
  entryRoute: 'custom_field_list'
  entrySidebar: 'Y'
  menuShow: 'Y'
  role: ['ROLE_SUPPORT']
-
  name: 'Demonstration Data'
  precedence: 0
  category: 'Extend & Update'
  description: 'Load Demonstration Data'
  route_list: ['demonstration_load']
  entry_route: 'demonstration_load'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_SYSTEM_ADMIN']
-
  name: 'Student Settings'
  precedence: 0
  category: 'Settings'
  description: 'Configure settings relating to Students and Student Note Categories'
  route_list: ['student_settings','student_note_category_edit','student_note_category_add','student_note_category_delete']
  entry_route: 'student_settings'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'Student Settings'
  precedence: 0
  category: 'Settings'
  description: 'Configure settings relating to Students and Student Note Categories'
  route_list: ['student_settings_people']
  entry_route: 'student_settings_people'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'Academic Years'
  precedence: 0
  category: 'Years, Days & Times'
  description: 'Allows user to control the definition of academic years within the system.'
  route_list: ['academic_year_list','academic_year_edit','academic_year_delete','academic_year_add','academic_year_display_popup_raw']
  entry_route: 'academic_year_list'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_PRINCIPAL']
-
  name: 'Staff Settings'
  precedence: 0
  category: 'Settings'
  description: 'Configure settings relating to Students and Student Note Categories'
  route_list: ['staff_settings','staff_absence_type_add','staff_absence_type_edit','staff_absence_type_delete','staff_absence_type_sort']
  entry_route: 'staff_settings'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_REGISTRAR']
-
  name: 'Staff Settings'
  precedence: 0
  category: 'Settings'
  description: 'Configure settings relating to Students and Student Note Categories'
  route_list: ['staff_settings_people']
  entry_route: 'staff_settings_people'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_SUPPORT']
-
  name: 'Academic Year Terms'
  precedence: 0
  category: 'Years, Days & Times'
  description: 'Allows user to control the definition of academic year terms within the system.'
  route_list: ['academic_year_term_list','academic_year_term_edit','academic_year_term_delete','academic_year_term_add']
  entry_route: 'academic_year_term_list'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_PRINCIPAL']
-
  name: 'Special Days'
  precedence: 0
  category: 'Years, Days & Times'
  description: 'Allows user to control the definition of special days within the system.'
  route_list: ['special_day_list','special_day_edit','special_day_delete','special_day_add','special_day_duplicate','special_day_filter_store']
  entry_route: 'special_day_list'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_PRINCIPAL']
-
  name: 'Days of the Week'
  category: 'Years, Days & Times'
  description: 'Manage the Days of the week for your system.'
  route_list: ['days_of_the_week']
  entry_route: 'days_of_the_week'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_PRINCIPAL']
-
  name: 'Mark Book Settings'
  category: 'Assess'
  description: 'Configure options for the Mark Book Module'
  route_list: ['mark_book_settings']
  entry_route: 'mark_book_settings'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_PRINCIPAL']
-
  name: 'Scales'
  category: 'Assess'
  description: 'Manage all aspects of grade scales, which are used throughout ARR to control grade input.'
  route_list: ['scale_list','scale_edit','scale_add','scale_delete','scale_grade_edit','scale_grade_add','scale_grade_delete','scale_grade_sort']
  entry_route: 'scale_list'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_PRINCIPAL']
-
  name: 'Mark Book Settings'
  category: 'Configure'
  description: 'Configure options for the Mark Book Module'
  route_list: ['mark_book_configure']
  entry_route: 'mark_book_configure'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_PRINCIPAL']
  module: '8282b1f4-a489-11ea-8213-74d435a91d85'
-
  name: 'View Mark Book_allClassesAllData'
  precedence: 4
  category: 'Mark Book'
  description: 'View all mark book information for all users'
  route_list: ['mark_book_view']
  entry_route: 'mark_book_view'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_PRINCIPAL']
  module: '8282b1f4-a489-11ea-8213-74d435a91d85'
-
  name: 'Year Groups'
  category: 'Groupings'
  description: ''
  route_list: ['year_group_list','year_group_edit','year_group_add','year_group_delete','year_group_sort']
  entry_route: 'year_group_list'
  entry_sidebar: 'Y'
  menu_show: 'Y'
  role: ['ROLE_REGISTRAR']
");
    }

    /**
     * coreData
     * @return string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * isArrayField
     * @param string $name
     * @return bool
     */
    public function isArrayField(string $name): bool
    {
         return in_array($name, ['role','routeList']);
    }

    /**
     * coreDataLinks
     * @return mixed
     */
    public function coreDataLinks()
    {
        return Yaml::parse("
-
    findBy: 
        entryRoute: 'preferences'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'system_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: System }
    target: module
-
    findBy: 
        entryRoute: 'people_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'third_party_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: System }
    target: module
-
    findBy: 
        entryRoute: 'system_check'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: System }
    target: module
-
    findBy: 
        entryRoute: 'string_manage'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: System }
    target: module
-
    findBy: 
        entryRoute: 'notification_events'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: System }
    target: module
-
    findBy: 
        entryRoute: 'display_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: System }
    target: module
-
    findBy: 
        entryRoute: 'personal_photo_import'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'family_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'people_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'student_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: Students }
    target: module
-
    findBy: 
        entryRoute: 'student_view'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: Students }
    target: module
-
    findBy: 
        entryRoute: 'staff_view'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: Staff }
    target: module
-
    findBy: 
        entryRoute: 'updater_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'address_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'phone_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'locality_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'custom_field_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'demonstration_data'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: System }
    target: module
-
    findBy: 
        entryRoute: 'student_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'student_settings_people'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: System }
    target: module
-
    findBy: 
        entryRoute: 'academic_year_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: School }
    target: module
-
    findBy: 
        entryRoute: 'staff_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: Staff }
    target: module
-
    findBy: 
        entryRoute: 'staff_settings_people'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: People }
    target: module
-
    findBy: 
        entryRoute: 'academic_year_term_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: School }
    target: module
-
    findBy: 
        entryRoute: 'special_day_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: School }
    target: module
-
    findBy: 
        entryRoute: 'days_of_the_week'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: School }
    target: module
-
    findBy: 
        entryRoute: 'scale_list'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: School }
    target: module
-
    findBy: 
        entryRoute: 'mark_book_settings'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: School }
    target: module
-
    findBy: 
        entryRoute: 'mark_book_configure'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: 'Mark Book' }
    target: module
-
    findBy: 
        entryRoute: 'mark_book_view'
    source: 
        table: App\Modules\System\Entity\Module
        findBy: { name: 'Mark Book' }
    target: module
-
    findBy: 
        entryRoute: 'year_group_list'
    source:
        table: App\Modules\System\Entity\Module
        findBy: { name: 'School' }
    target: module
");
    }
}
