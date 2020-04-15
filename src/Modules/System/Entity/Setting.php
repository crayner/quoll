<?php
/**
 * Created by PhpStorm.
 *
* Kookaburra
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 25/11/2018
 * Time: 10:00
 */
namespace App\Modules\System\Entity;

use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Setting
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\SettingRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Setting",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="scope_display", columns={"scope","nameDisplay"}),
 *     @ORM\UniqueConstraint(name="scope_name", columns={"scope","name"})})
 */
class Setting implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(5) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Setting
     */
    public function setId(?int $id): Setting
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $scope;

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string|null $scope
     * @return Setting
     */
    public function setScope(?string $scope): Setting
    {
        $this->scope = mb_substr($scope, 0, 50);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Setting
     */
    public function setName(?string $name): Setting
    {
        $this->name = mb_substr($name, 0, 50);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=60, name="nameDisplay")
     */
    private $nameDisplay;

    /**
     * @return string|null
     */
    public function getNameDisplay(): ?string
    {
        return $this->nameDisplay;
    }

    /**
     * @param string|null $nameDisplay
     * @return Setting
     */
    public function setNameDisplay(?string $nameDisplay): Setting
    {
        $this->nameDisplay = mb_substr($nameDisplay, 0, 60);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column()
     */
    private $description;

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Setting
     */
    public function setDescription(?string $description): Setting
    {
        $this->description = mb_substr($description, 0, 255);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return Setting
     */
    public function setValue(?string $value): Setting
    {
        $this->value = $value;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    public function create(): string
    {
        return 'CREATE TABLE `__prefix__Setting` (
                    `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `scope` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `nameDisplay` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `value` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `scope_name` (`scope`,`name`) USING BTREE,
                    UNIQUE KEY `scope_display` (`scope`,`nameDisplay`) USING BTREE
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public function coreData(): string
    {
        return "INSERT INTO `__prefix__Setting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES
('System', 'absoluteURL', 'Base URL', 'The address at which the whole system resides.', null),
('System', 'organisationName', 'Organisation Name', '', null),
('System', 'organisationNameShort', 'Organisation Initials', '', null),
('System', 'pagination', 'Pagination Count', 'Must be numeric. Number of records shown per page.', '50'),
('System', 'systemName', 'System Name', '', 'Quoll'),
('System', 'indexText', 'Index Page Text', 'Text displayed in system\'s welcome page.', 'Welcome to Kookaburra, the free, open, flexible school platform. Designed by teachers for learning, Kookaburra gives you the school tools you need. Kookaburra is a fork of Gibbon.'),
('System', 'absolutePath', 'Base Path', 'The local FS path to the system', null),
('System', 'timezone', 'Timezone', 'The timezone where the school is located', 'UTC'),
('System', 'analytics', 'Analytics', 'Javascript code to integrate statistics, such as Google Analytics', ''),
('System', 'emailLink', 'Link To Email', 'The link that points to the school/\'s email system', ''),
('System', 'webLink', 'Link To Web', 'The link that points to the school/\'s website', NULL),
('System', 'defaultAssessmentScale', 'Default Assessment Scale', 'This is the scale used as a default where assessment scales need to be selected.', null),
('System', 'country', 'Country', 'The country the school is located in', 'AU'),
('System', 'organisationLogo', 'Logo', 'Relative path to site logo (400 x 100px)', '\\themes\\Default\\img\\logo.png'),
('System', 'calendarFeed', 'School Google Calendar ID', 'Google Calendar ID for your school calendar. Only enables timetable integration when logging in via Google.', 'craig@craigrayner.com'),
('Activities', 'access', 'Access', 'System-wide access control', 'Register'),
('Activities', 'payment', 'Payment', 'Payment system', 'Per Activity'),
('Activities', 'enrolmentType', 'Enrolment Type', 'Enrolment process type', 'Competitive'),
('Activities', 'backupChoice', 'Backup Choice', 'Allow students to choose a backup, in case enroled activity is full.', 'Y'),
('Activities', 'activityTypes', 'Activity Types', 'Comma-seperated list of the different activity types available in school. Leave blank to disable this feature.', 'Creativity,Action,Service'),
('Application Form', 'introduction', 'Introduction', 'Information to display before the form', ''),
('Application Form', 'postscript', 'Postscript', 'Information to display at the end of the form', ''),
('Application Form', 'scholarships', 'Scholarships', 'Information to display before the scholarship options', ''),
('Application Form', 'agreement', 'Agreement', 'Without this text, which is displayed above the agreement, users will not be asked to agree to anything', ''),
('Application Form', 'publicApplications', 'Public Applications?', 'If yes, members of the public can submit applications', 'Y'),
('Behaviour', 'positiveDescriptors', 'Positive Descriptors', 'Allowable choices for positive behaviour', 'Attitude to learning,Collaboration,Community spirit,Creativity,Effort,Leadership,Participation,Persistence,Problem solving,Quality of work,Values'),
('Behaviour', 'negativeDescriptors', 'Negative Descriptors', 'Allowable choices for negative behaviour', 'Classwork - Late,Classwork - Incomplete,Classwork - Unacceptable,Disrespectful,Disruptive,Homework - Late,Homework - Incomplete,Homework - Unacceptable,ICT Misuse,Truancy,Other'),
('Behaviour', 'levels', 'Levels', 'Allowable choices for severity level (from lowest to highest)', ',Stage 1,Stage 1 (Actioned),Stage 2,Stage 2 (Actioned),Stage 3,Stage 3 (Actioned),Actioned'),
('Resources', 'categories', 'Categories', 'Allowable choices for category', 'Article,Book,Document,Graphic,Idea,Music,Object,Painting,Person,Photo,Place,Poetry,Prose,Rubric,Text,Video,Website,Work Sample,Other'),
('Resources', 'purposesGeneral', 'Purposes (General)', 'Allowable choices for purpose when creating a resource', 'Assessment Aid,Concept,Inspiration,Learner Profile,Mass Mailer Attachment,Provocation,Skill,Teaching and Learning Strategy,Other'),
('System', 'version', 'Version', 'The version of the Gibbon database', '18.0.00'),
('Resources', 'purposesRestricted', 'Purposes (Restricted)', 'Additional allowable choices for purpose when creating a resource, for those with \"Manage All Resources\" rights', ''),
('System', 'organisationEmail', 'Organisation Email', 'General email address for the school', ''),
('Activities', 'dateType', 'Date Type', 'Should activities be organised around dates (flexible) or terms (easy)?', 'Term'),
('System', 'installType', 'Install Type', 'The purpose of this installation of Kookaburra', 'Development'),
('System', 'statsCollection', 'Statistics Collection', 'To track Gibbon uptake, the system tracks basic data (current URL, install type, organisation name) on each install. Do you want to help?', 'Y'),
('Activities', 'maxPerTerm', 'Maximum Activities per Term', 'The most a student can sign up for in one term. Set to 0 for unlimited.', '0'),
('Planner', 'lessonDetailsTemplate', 'Lesson Details Template', 'Template to be inserted into Lesson Details field', NULL),
('Planner', 'teachersNotesTemplate', 'Teacher\'s Notes Template', 'Template to be inserted into Teacher\'s Notes field', NULL),
('Planner', 'smartBlockTemplate', 'Smart Block Template', 'Template to be inserted into new block in Smart Unit', NULL),
('Planner', 'unitOutlineTemplate', 'Unit Outline Template', 'Template to be inserted into Unit Outline section of planner', NULL),
('Application Form', 'milestones', 'Milestones', 'Comma-separated list of the major steps in the application process. Applicants can be tracked through the various stages.', ''),
('Library', 'defaultLoanLength', 'Default Loan Length', 'The standard loan length for a library item, in days', '7'),
('Behaviour', 'policyLink', 'Policy Link', 'A link to the school behaviour policy.', NULL),
('Library', 'browseBGColor', 'Browse Library BG Color', 'RGB Hex value, without leading #. Background color used behind library browsing screen.', ''),
('Library', 'browseBGImage', 'Browse Library BG Image', 'URL to background image used behind library browsing screen.', ''),
('System', 'passwordPolicyAlpha', 'Password - Alpha Requirement', 'Require both upper and lower case alpha characters?', 'Y'),
('System', 'passwordPolicyNumeric', 'Password - Numeric Requirement', 'Require at least one numeric character?', 'Y'),
('System', 'passwordPolicyNonAlphaNumeric', 'Password - Non-Alphanumeric Requirement', 'Require at least one non-alphanumeric character (e.g. punctuation mark or space)?', 'N'),
('System', 'passwordPolicyMinLength', 'Password - Minimum Length', 'Minimum acceptable password length.', '8'),
('User Admin', 'ethnicity', 'Ethnicity', 'Comma-separated list of ethnicities available in system', ''),
('User Admin', 'nationality', 'Nationality', 'Comma-separated list of nationalities available in system. If blank, system will default to list of countries', ''),
('User Admin', 'residencyStatus', 'Residency Status', 'Comma-separated list of residency status available in system. If blank, system will allow text input', ''),
('User Admin', 'personalDataUpdaterRequiredFields', 'Personal Data Updater Required Fields', 'Serialized array listed personal fields in data updater, and whether or not they are required.', 'a:47:{s:5:\"title\";s:1:\"N\";s:7:\"surname\";s:1:\"Y\";s:9:\"firstName\";s:1:\"N\";s:10:\"otherNames\";s:1:\"N\";s:13:\"preferredName\";s:1:\"Y\";s:12:\"officialName\";s:1:\"Y\";s:16:\"nameInCharacters\";s:1:\"N\";s:3:\"dob\";s:1:\"N\";s:5:\"email\";s:1:\"N\";s:14:\"emailAlternate\";s:1:\"N\";s:8:\"address1\";s:1:\"Y\";s:16:\"address1District\";s:1:\"N\";s:15:\"address1Country\";s:1:\"N\";s:8:\"address2\";s:1:\"N\";s:16:\"address2District\";s:1:\"N\";s:15:\"address2Country\";s:1:\"N\";s:10:\"phone1Type\";s:1:\"N\";s:17:\"phone1CountryCode\";s:1:\"N\";s:6:\"phone1\";s:1:\"N\";s:6:\"phone2\";s:1:\"N\";s:6:\"phone3\";s:1:\"N\";s:6:\"phone4\";s:1:\"N\";s:13:\"languageFirst\";s:1:\"N\";s:14:\"languageSecond\";s:1:\"N\";s:13:\"languageThird\";s:1:\"N\";s:14:\"countryOfBirth\";s:1:\"N\";s:9:\"ethnicity\";s:1:\"N\";s:12:\"citizenship1\";s:1:\"N\";s:20:\"citizenship1Passport\";s:1:\"N\";s:12:\"citizenship2\";s:1:\"N\";s:20:\"citizenship2Passport\";s:1:\"N\";s:8:\"religion\";s:1:\"N\";s:20:\"nationalIDCardNumber\";s:1:\"N\";s:15:\"residencyStatus\";s:1:\"N\";s:14:\"visaExpiryDate\";s:1:\"N\";s:10:\"profession\";s:1:\"N\";s:8:\"employer\";s:1:\"N\";s:8:\"jobTitle\";s:1:\"N\";s:14:\"emergency1Name\";s:1:\"N\";s:17:\"emergency1Number1\";s:1:\"N\";s:17:\"emergency1Number2\";s:1:\"N\";s:22:\"emergency1Relationship\";s:1:\"N\";s:14:\"emergency2Name\";s:1:\"N\";s:17:\"emergency2Number1\";s:1:\"N\";s:17:\"emergency2Number2\";s:1:\"N\";s:22:\"emergency2Relationship\";s:1:\"N\";s:19:\"vehicleRegistration\";s:1:\"N\";}'),
('School Admin', 'primaryExternalAssessmentByYearGroup', 'Primary External Assessment By Year Group', 'Serialized array connected gibbonExternalAssessmentID to gibbonYearGroupID, and specify which field set to use.', 'a:7:{i:1;s:21:\"1 - 2_KS3 Target Grades\";i:2;s:22:\"1 - 3_GCSE Target Grades\";i:3;s:10:\"1 - 1_Scores\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";}'),
('Markbook', 'markbookType', 'Markbook Type', 'Comma-separated list of types to make available in the Markbook.', 'Essay,Exam,Homework,Reflection,Test,Unit,End of Year,Other'),
('System', 'allowableHTML', 'Allowable HTML', 'TinyMCE-style list of acceptable HTML tags and options.', 'br[style],strong[style],em[style],span[style],p[style],address[style],pre[style],h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],table[style],thead[style],tbody[style],tfoot[style],tr[style],td[style|colspan|rowspan],ol[style],ul[style],li[style],blockquote[style],a[style|target|href],img[style|class|src|width|height],video[style],source[style],hr[style],iframe[style|width|height|src|frameborder|allowfullscreen],embed[style],div[style],sup[style],sub[style]'),
('Application Form', 'howDidYouHear', 'How Did Your Hear?', 'Comma-separated list', 'Advertisement,Personal Recommendation,World Wide Web,Others'),
('Messenger', 'smsUsername', 'SMS Username', 'SMS gateway username.', ''),
('Messenger', 'smsPassword', 'SMS Password', 'SMS gateway password.', ''),
('Messenger', 'smsURL', 'SMS URL', 'SMS gateway URL for send requests.', ''),
('Messenger', 'smsURLCredit', 'SMS URL Credit', 'SMS gateway URL for checking credit.', ''),
('System', 'currency', 'Currency', 'System-wide currency for financial transactions. Support for online payment in this currency depends on your credit card gateway: please consult their support documentation.', 'AUD'),
('System', 'enablePayments', 'Enable Payments', 'Should payments be enabled across the system?', 'N'),
('System', 'paypalAPIUsername', 'PayPal API Username', 'API Username provided by PayPal.', ''),
('System', 'paypalAPIPassword', 'PayPal API Password', 'API Password provided by PayPal.', ''),
('System', 'paypalAPISignature', 'PayPal API Signature', 'API Signature provided by PayPal.', ''),
('Application Form', 'applicationFee', 'Application Fee', 'The cost of applying to the school.', '0'),
('Application Form', 'requiredDocuments', 'Required Documents', 'Comma-separated list of documents which must be submitted electronically with the application form.', ''),
('Application Form', 'requiredDocumentsCompulsory', 'Required Documents Compulsory?', 'Are the required documents compulsory?', 'N'),
('Application Form', 'requiredDocumentsText', 'Required Documents Text', 'Explanatory text to appear with the required documents?', ''),
('Application Form', 'notificationStudentDefault', 'Student Notification Default', 'Should student acceptance email be turned on or off by default.', 'On'),
('Application Form', 'languageOptionsActive', 'Language Options Active', 'Should the Language Options section be turned on?', 'Off'),
('Application Form', 'languageOptionsBlurb', 'Language Options Blurb', 'Introductory text if Language Options section is turned on.', ''),
('Application Form', 'languageOptionsLanguageList', 'Language Options Language List', 'Comma-separated list of available language selections if Language Options section is turned on.', ''),
('User Admin', 'personalBackground', 'Personal Background', 'Should users be allowed to set their own personal backgrounds?', 'Y'),
('User Admin', 'dayTypeOptions', 'Day-Type Options', 'Comma-separated list of options to make available (e.g. half-day, full-day). If blank, this field will not show up in the application form.', ''),
('User Admin', 'dayTypeText', 'Day-Type Text', 'Explanatory text to include with Day-Type Options.', ''),
('Markbook', 'showStudentAttainmentWarning', 'Show Student Attainment Warning', 'Show low attainment grade visual warning to students?', 'Y'),
('Markbook', 'showStudentEffortWarning', 'Show Student Effort Warning', 'Show low effort grade visual warning to students?', 'Y'),
('Markbook', 'showParentAttainmentWarning', 'Show Parent Attainment Warning', 'Show low attainment grade visual warning to parents?', 'Y'),
('Markbook', 'showParentEffortWarning', 'Show Parent Effort Warning', 'Show low effort grade visual warning to parents?', 'Y'),
('Planner', 'allowOutcomeEditing', 'Allow Outcome Editing', 'Should the text within outcomes be editable when planning lessons and units?', 'Y'),
('User Admin', 'privacy', 'Privacy', 'Should privacy options be turned on across the system?', 'N'),
('User Admin', 'privacyBlurb', 'Privacy Blurb', 'Descriptive text to accompany image privacy option when shown to users.', ''),
('Finance', 'invoiceText', 'Invoice Text', 'Text to appear in invoice, above invoice details and fees.', NULL),
('Finance', 'invoiceNotes', 'Invoice Notes', 'Text to appear in invoice, below invoice details and fees.', NULL),
('Finance', 'receiptText', 'Receipt Text', 'Text to appear in receipt, above receipt details and fees.', NULL),
('Finance', 'receiptNotes', 'Receipt Notes', 'Text to appear in receipt, below receipt details and fees.', NULL),
('Finance', 'reminder1Text', 'Reminder 1 Text', 'Text to appear in first level reminder level, above invoice details and fees.', NULL),
('Finance', 'reminder2Text', 'Reminder 2 Text', 'Text to appear in second level reminder level, above invoice details and fees.', NULL),
('Finance', 'reminder3Text', 'Reminder 3 Text', 'Text to appear in third level reminder level, above invoice details and fees.', NULL),
('Finance', 'email', 'Email', 'Email address to send finance emails from.', 'craig@craigrayner.com'),
('Application Form', 'notificationParentsDefault', 'Parents Notification Default', 'Should parent acceptance email be turned on or off by default.', 'On'),
('User Admin', 'privacyOptions', 'Privacy Options', 'Comma-separated list of choices to make available if privacy options are turned on. If blank, privacy fields will not be displayed.', ''),
('Planner', 'sharingDefaultParents', 'Sharing Default: Parents', 'When adding lessons and deploying units, should sharing default for parents be Y or N?', 'Y'),
('Planner', 'sharingDefaultStudents', 'Sharing Default: Students', 'When adding lessons and deploying units, should sharing default for students be Y or N?', 'Y'),
('Students', 'extendedBriefProfile', 'Extended Brief Profile', 'The extended version of the brief student profile includes contact information of parents.', 'N'),
('Application Form', 'notificationParentsMessage', 'Parents Notification Message', 'A custom message to add to the standard email to parents on acceptance.', ''),
('Application Form', 'notificationStudentMessage', 'Student Notification Message', 'A custom message to add to the standard email to students on acceptance.', ''),
('Finance', 'invoiceNumber', 'Invoice Number Style', 'How should invoice numbers be constructed?', 'Invoice ID'),
('User Admin', 'departureReasons', 'Departure Reasons', 'Comma-separated list of reasons for departure from school. If blank, user can enter any text.', ''),
('System', 'googleOAuth', 'Google Integration', 'Enable Gibbon-wide integration with the Google APIs?', 'Y'),
('System', 'googleClientName', 'Google Developers Client Name', 'Name of Google Project in Developers Console.', 'gibbon-231623'),
('System', 'googleClientID', 'Google Developers Client ID', 'Client ID for Google Project In Developers Console.', '869932302474-vmp86mrilkcn37s62vhrjpcdq2fu3ava.apps.googleusercontent.com'),
('System', 'googleClientSecret', 'Google Developers Client Secret', 'Client Secret for Google Project In Developers Console.', 'jqgOQUB_b2ms7DftqXvA4JSR'),
('System', 'googleRedirectUri', 'Google Developers Redirect Url', 'Google Redirect on sucessful auth.', 'https://bilby.craigrayner.com/security/oauth2callback/'),
('System', 'googleDeveloperKey', 'Google Developers Developer Key', 'Google project Developer Key.', 'AIzaSyDxs2So92a--QPgNXfTYeAPK2EyVL4XZ2Q'),
('Markbook', 'personalisedWarnings', 'Personalised Warnings', 'Should markbook warnings be based on personal targets, if they are available?', 'Y'),
('Activities', 'disableExternalProviderSignup', 'Disable External Provider Signup', 'Should we turn off the option to sign up for activities provided by an outside agency?', 'N'),
('Activities', 'hideExternalProviderCost', 'Hide External Provider Cost', 'Should we hide the cost of activities provided by an outside agency from the Activities View?', 'N'),
('Application Form', 'studentDefaultEmail', 'Student Default Email', 'Set default email for students on acceptance, using [username] to insert username.', ''),
('Application Form', 'studentDefaultWebsite', 'Student Default Website', 'Set default website for students on acceptance, using [username] to insert username.', ''),
('School Admin', 'studentAgreementOptions', 'Student Agreement Options', 'Comma-separated list of agreements that students might be asked to sign in school (e.g. ICT Policy).', ''),
('Markbook', 'attainmentAlternativeName', 'Attainment Alternative Name', 'A name to use isntead of \"Attainment\" in the first grade column of the markbook.', NULL),
('Markbook', 'effortAlternativeName', 'Effort Alternative Name', 'A name to use isntead of \"Effort\" in the second grade column of the markbook.', NULL),
('Markbook', 'attainmentAlternativeNameAbrev', 'Attainment Alternative Name Abbreviation', 'A short name to use isntead of \"Attainment\" in the first grade column of the markbook.', NULL),
('Markbook', 'effortAlternativeNameAbrev', 'Effort Alternative Name Abbreviation', 'A short name to use isntead of \"Effort\" in the second grade column of the markbook.', NULL),
('Planner', 'parentWeeklyEmailSummaryIncludeBehaviour', 'Parent Weekly Email Summary Include Behaviour', 'Should behaviour information be included in the weekly planner email summary that goes out to parents?', 'Y'),
('Finance', 'financeOnlinePaymentEnabled', 'Enable Online Payment', 'Should invoices be payable online, via an encrypted link in the invoice? Requires correctly configured payment gateway in System Settings.', 'N'),
('Finance', 'financeOnlinePaymentThreshold', 'Online Payment Threshold', 'If invoices are payable online, what is the maximum payment allowed? Useful for controlling payment fees. No value means unlimited.', NULL),
('Departments', 'makeDepartmentsPublic', 'Make Departments Public', 'Should department information be made available to the public, via the Gibbon homepage?', 'Y'),
('System', 'sessionDuration', 'Session Duration', 'Time, in seconds, before system logs a user out. Should be less than PHP\'s session.gc_maxlifetime option.', '1200'),
('Planner', 'makeUnitsPublic', 'Make Units Public', 'Enables a public listing of units, with teachers able to opt in to share units.', 'Y'),
('Messenger', 'messageBubbleWidthType', 'Message Bubble Width Type', 'Should the message bubble be regular or wide?', 'Regular'),
('Messenger', 'messageBubbleBGColor', 'Message Bubble Background Color', 'Message bubble background color in RGBA (e.g. 100,100,100,0.50). If blank, theme default will be used.', NULL),
('Messenger', 'messageBubbleAutoHide', 'Message Bubble Auto Hide', 'Should message bubble fade out automatically?', 'Y'),
('Students', 'enableStudentNotes', 'Enable Student Notes', 'Should student notes be turned on?', 'Y'),
('Finance', 'budgetCategories', 'Budget Categories', 'Comma-separated list of budget categories.', 'Academic,Administration,Capital'),
('Finance', 'expenseApprovalType', 'Expense Approval Type', 'How should expense approval be dealt with?', 'One Of'),
('Finance', 'budgetLevelExpenseApproval', 'Budget Level Expense Approval', 'Should approval from a budget member with Full access be required?', 'Y'),
('Finance', 'expenseRequestTemplate', 'Expense Request Template', 'An HTML template to be used in the description field of expense requests.', NULL),
('Finance', 'purchasingOfficer', 'Purchasing Officer', 'User responsible for purchasing for the school.', NULL),
('Finance', 'reimbursementOfficer', 'Reimbursement Officer', 'User responsible for reimbursing expenses.', NULL),
('Messenger', 'enableHomeScreenWidget', 'Enable Home Screen Widget', 'Adds a Message Wall widget to the home page, hihglighting current messages.', 'N'),
('User Admin', 'enablePublicRegistration', 'Enable Public Registration', 'Allows members of the public to register to use the system.', 'Y'),
('User Admin', 'publicRegistrationMinimumAge', 'Public Registration Minimum Age', 'The minimum age, in years, permitted to register.', '13'),
('User Admin', 'publicRegistrationDefaultStatus', 'Public Registration Default Status', 'Should new users be \'Full\' or \'Pending Approval\'?', 'Pending Approval'),
('User Admin', 'publicRegistrationDefaultRole', 'Public Registration Default Role', 'System role to be assigned to registering members of the public.', '3'),
('User Admin', 'publicRegistrationIntro', 'Public Registration Introductory Text', 'HTML text that will appear above the public registration form.', NULL),
('User Admin', 'publicRegistrationPrivacyStatement', 'Public Registration Privacy Statement', 'HTML text that will appear above the Submit button, explaining privacy policy.', 'By registering for this site you are giving permission for your personal data to be used and shared within this organisation and its websites. We will not share your personal data outside our organisation.'),
('User Admin', 'publicRegistrationAgreement', 'Public Registration Agreement', 'Agreement that user must confirm before joining. Blank for no agreement.', 'In joining this site, and checking the box below, I agree to act lawfully, ethically and with respect for others. I agree to use this site for learning purposes only, and understand that access may be withdrawn at any time, at the discretion of the site\'s administrators.'),
('User Admin', 'publicRegistrationPostscript', 'Public Registration Postscript', 'HTML text that will appear underneath the public registration form.', NULL),
('Behaviour', 'enableDescriptors', 'Enable Descriptors', 'Setting to No reduces complexity of behaviour tracking.', 'Y'),
('Behaviour', 'enableLevels', 'Enable Levels', 'Setting to No reduces complexity of behaviour tracking.', 'Y'),
('Formal Assessment', 'internalAssessmentTypes', 'Internal Assessment Types', 'Comma-separated list of types to make available in Internal Assessments.', 'Expected Grade,Predicted Grade,Target Grade'),
('System Admin', 'customAlarmSound', 'Custom Alarm Sound', 'A custom alarm sound file.', ''),
('School Admin', 'facilityTypes', 'FacilityTypes', 'A comma-separated list of types for facilities.', 'Classroom,Hall,Laboratory,Library,Office,Outdoor,Performance,Staffroom,Storage,Study,Undercover,Other'),
('Finance', 'allowExpenseAdd', 'Allow Expense Add', 'Allows privileged users to add expenses without going through request process.', 'Y'),
('System', 'organisationAdministrator', 'System Administrator', 'The staff member who receives notifications for system events.', '1'),
('System', 'organisationDBA', 'Database Administrator', 'The staff member who receives notifications for data events.', '1'),
('System', 'organisationAdmissions', 'Admissions Administrator', 'The staff member who receives notifications for admissions events.', '1'),
('Finance', 'hideItemisation', 'Hide Itemisation', 'Hide fee and payment details in receipts?', 'N'),
('Application Form', 'autoHouseAssign', 'Auto House Assign', 'Attempt to automatically place student in a house?', 'N'),
('Tracking', 'externalAssessmentDataPoints', 'External Assessment Data Points', 'Stores the external assessment choices for data points output in tracking.', 'a:7:{i:0;a:3:{s:10:\"assessment\";s:1:\"1\";s:8:\"category\";s:8:\"1_Scores\";s:13:\"yearGroupList\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"2\";i:2;s:1:\"3\";}}i:1;a:3:{s:10:\"assessment\";s:1:\"1\";s:8:\"category\";s:19:\"2_KS3 Target Grades\";s:13:\"yearGroupList\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"2\";i:2;s:1:\"3\";}}i:2;a:3:{s:10:\"assessment\";s:1:\"1\";s:8:\"category\";s:20:\"3_GCSE Target Grades\";s:13:\"yearGroupList\";a:2:{i:0;s:1:\"4\";i:1;s:1:\"5\";}}i:3;a:3:{s:10:\"assessment\";s:1:\"2\";s:8:\"category\";s:14:\"2_Target Grade\";s:13:\"yearGroupList\";a:2:{i:0;s:1:\"4\";i:1;s:1:\"5\";}}i:4;a:3:{s:10:\"assessment\";s:1:\"2\";s:8:\"category\";s:13:\"1_Final Grade\";s:13:\"yearGroupList\";a:2:{i:0;s:1:\"4\";i:1;s:1:\"5\";}}i:5;a:3:{s:10:\"assessment\";s:1:\"3\";s:8:\"category\";s:14:\"2_Target Grade\";s:13:\"yearGroupList\";a:2:{i:0;s:1:\"6\";i:1;s:1:\"7\";}}i:6;a:3:{s:10:\"assessment\";s:1:\"3\";s:8:\"category\";s:13:\"1_Final Grade\";s:13:\"yearGroupList\";a:2:{i:0;s:1:\"6\";i:1;s:1:\"7\";}}}'),
('Tracking', 'internalAssessmentDataPoints', 'Internal Assessment Data Points', 'Stores the internal assessment choices for data points output in tracking.', 'a:3:{i:0;a:2:{s:4:\"type\";s:14:\"Expected Grade\";s:13:\"yearGroupList\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:1;a:2:{s:4:\"type\";s:15:\"Predicted Grade\";s:13:\"yearGroupList\";a:2:{i:0;i:4;i:1;i:5;}}i:2;a:2:{s:4:\"type\";s:12:\"Target Grade\";s:13:\"yearGroupList\";a:2:{i:0;i:6;i:1;i:7;}}}'),
('Behaviour', 'enableBehaviourLetters', 'Enable Behaviour Letters', 'Should automated behaviour letter functionality be enabled?', 'N'),
('Behaviour', 'behaviourLettersLetter1Count', 'Letter 1 Count', 'After how many negative records should letter 1 be sent?', '3'),
('Behaviour', 'behaviourLettersLetter1Text', 'Letter 1 Text', 'The contents of letter 1, as HTML.', 'Dear Parent/Guardian,<br/><br/>This letter has been automatically generated to alert you to the fact that your child, [studentName], has reached [behaviourCount] negative behaviour incidents. Please see the list below for the details of these incidents:<br/><br/>[behaviourRecord]<br/><br/>This letter represents the first communication in a sequence of 3 potential alerts, each of which is more critical than the last.<br/><br/>If you would like more information on this matter, please contact your child\'s tutor.'),
('Behaviour', 'behaviourLettersLetter2Count', 'Letter 2 Count', 'After how many negative records should letter 2 be sent?', '6'),
('Behaviour', 'behaviourLettersLetter2Text', 'Letter 2 Text', 'The contents of letter 2, as HTML.', 'Dear Parent/Guardian,<br/><br/>This letter has been automatically generated to alert you to the fact that your child, [studentName], has reached [behaviourCount] negative behaviour incidents. Please see the list below for the details of these incidents:<br/><br/>[behaviourRecord]<br/><br/>This letter represents the second communication in a sequence of 3 potential alerts, each of which is more critical than the last.<br/><br/>If you would like more information on this matter, please contact your child\'s tutor.'),
('Behaviour', 'behaviourLettersLetter3Count', 'Letter 3 Count', 'After how many negative records should letter 3 be sent?', '9'),
('Behaviour', 'behaviourLettersLetter3Text', 'Letter 3 Text', 'The contents of letter 3, as HTML.', 'Dear Parent/Guardian,<br/><br/>This letter has been automatically generated to alert you to the fact that your child, [studentName], has reached [behaviourCount] negative behaviour incidents. Please see the list below for the details of these incidents:<br/><br/>[behaviourRecord]<br/><br/>This letter represents the final communication in a sequence of 3 potential alerts, each of which is more critical than the last.<br/><br/>If you would like more information on this matter, please contact your child\'s tutor.'),
('Markbook', 'enableColumnWeighting', 'Enable Column Weighting', 'Should column weighting and total scores be enabled in the Markbook?', 'N'),
('System', 'firstDayOfTheWeek', 'First Day Of The Week', 'On which day should the week begin?', 'Monday'),
('Application Form', 'usernameFormat', 'Username Format', 'How should usernames be formated? Choose from [preferredName], [preferredNameInitial], [surname].', '[preferredNameInitial][surname]'),
('Staff', 'jobOpeningDescriptionTemplate', 'Job Opening Description Template', 'Default HTML contents for the Job Opening Description field.', '<table style=\'width: 100%\'>\n	<tr>\n		<td colspan=2 style=\'vertical-align: top\'>\n			<span style=\'text-decoration: underline; font-weight: bold\'>Job Description</span><br/>\n			<br/>\n		</td>\n	</tr>\n	<tr>\n		<td style=\'width: 50%; vertical-align: top\'>\n			<span style=\'text-decoration: underline; font-weight: bold\'>Responsibilities</span><br/>\n			<ul style=\'margin-top:0px\'>\n				<li></li>\n				<li></li>\n			</ul>\n		</td>\n		<td style=\'width: 50%; vertical-align: top\'>\n			<span style=\'text-decoration: underline; font-weight: bold\'>Required Skills/Characteristics</span><br/>\n			<ul style=\'margin-top:0px\'>\n				<li></li>\n				<li></li>\n			</ul>\n		</td>\n	</tr>\n	<tr>\n		<td style=\'width: 50%; vertical-align: top\'>\n			<span style=\'text-decoration: underline; font-weight: bold\'>Remuneration</span><br/>\n			<ul style=\'margin-top:0px\'>\n				<li></li>\n				<li></li>\n			</ul>\n		</td>\n		<td style=\'width: 50%; vertical-align: top\'>\n			<span style=\'text-decoration: underline; font-weight: bold\'>Other Details </span><br/>\n			<ul style=\'margin-top:0px\'>\n				<li></li>\n				<li></li>\n			</ul>\n		</td>\n	</tr>\n</table>'),
('Staff', 'staffApplicationFormIntroduction', 'Introduction', 'Information to display before the form', ''),
('Staff', 'staffApplicationFormPostscript', 'Postscript', 'Information to display at the end of the form', ''),
('Staff', 'staffApplicationFormAgreement', 'Agreement', 'Without this text, which is displayed above the agreement, users will not be asked to agree to anything', 'In submitting this form, I confirm that all information provided above is accurate and complete to the best of my knowledge.'),
('Staff', 'staffApplicationFormMilestones', 'Milestones', 'Comma-separated list of the major steps in the application process. Applicants can be tracked through the various stages.', 'Short List, First Interview, Second Interview, Offer Made, Offer Accepted, Contact Issued, Contact Signed'),
('Staff', 'staffApplicationFormRequiredDocuments', 'Required Documents', 'Comma-separated list of documents which must be submitted electronically with the application form.', 'Curriculum Vitae'),
('Staff', 'staffApplicationFormRequiredDocumentsCompulsory', 'Required Documents Compulsory?', 'Are the required documents compulsory?', 'Y'),
('Staff', 'staffApplicationFormRequiredDocumentsText', 'Required Documents Text', 'Explanatory text to appear with the required documents?', 'Please submit the following document(s) to ensure your application can be processed without delay.'),
('Staff', 'staffApplicationFormNotificationDefault', 'Notification Default', 'Should acceptance email be turned on or off by default.', 'Y'),
('Staff', 'staffApplicationFormNotificationMessage', 'Notification Message', 'A custom message to add to the standard email on acceptance.', ''),
('Staff', 'staffApplicationFormDefaultEmail', 'Default Email', 'Set default email on acceptance, using [username] to insert username.', ''),
('Staff', 'staffApplicationFormDefaultWebsite', 'Default Website', 'Set default website on acceptance, using [username] to insert username.', ''),
('Staff', 'staffApplicationFormUsernameFormat', 'Username Format', 'How should usernames be formated? Choose from [preferredName], [preferredNameInitial], [surname].', '[preferredNameInitial].[surname]'),
('System', 'organisationHR', 'Human Resources Administrator', 'The staff member who receives notifications for staffing events.', '0000000001'),
('Staff', 'staffApplicationFormQuestions', 'Application Questions', 'HTML text that will appear as questions for the applicant to answer.', '<span style=\'text-decoration: underline; font-weight: bold\'>Why are you applying for this role?</span><br/><p></p>'),
('Staff', 'salaryScalePositions', 'Salary Scale Positions', 'Comma-separated list of salary scale positions, from lowest to highest.', '1,2,3,4,5,6,7,8,9,10'),
('Staff', 'responsibilityPosts', 'Responsibility Posts', 'Comma-separated list of posts carrying extra responsibilities.', ''),
('Students', 'applicationFormSENText', 'Application Form SEN Text', 'Text to appear with the Special Educational Needs section of the student application form.', 'Please indicate whether or not your child has any known, or suspected, special educational needs, or whether they have been assessed for any such needs in the past. Provide any comments or information concerning your child\'s development that may be relevant to your child\'s performance in the classroom or elsewhere? Incorrect or withheld information may affect continued enrolment.'),
('Students', 'applicationFormRefereeLink', 'Application Form Referee Link', 'Link to an external form that will be emailed to a referee of the applicant\'s choosing.', ''),
('User Admin', 'religions', 'Religions', 'Comma-separated list of religions available in system', ',Nonreligious/Agnostic/Atheist,Buddhism,Christianity,Hinduism,Islam,Judaism,Other'),
('Staff', 'applicationFormRefereeLink', 'Application Form Referee Link', 'Link to an external form that will be emailed to a referee of the applicant\'s choosing.', ''),
('Markbook', 'enableRawAttainment', 'Enable Raw Attainment Marks', 'Should recording of raw marks be enabled in the Markbook?', 'N'),
('Markbook', 'enableGroupByTerm', 'Group Columns by Term', 'Should columns and total scores be grouped by term?', 'N'),
('Markbook', 'enableEffort', 'Enable Effort', 'Should columns have the Effort section enabled?', 'Y'),
('Markbook', 'enableRubrics', 'Enable Rubrics', 'Should columns have Rubrics section enabled?', 'Y'),
('School Admin', 'staffDashboardDefaultTab', 'Staff Dashboard Default Tab', 'The default landing tab for the staff dashboard.', NULL),
('School Admin', 'studentDashboardDefaultTab', 'Student Dashboard Default Tab', 'The default landing tab for the student dashboard.', NULL),
('School Admin', 'parentDashboardDefaultTab', 'Parent Dashboard Default Tab', 'The default landing tab for the parent dashboard.', 'Timetable'),
('System', 'enableMailerSMTP', 'Enable SMTP Mail', 'Adds PHPMailer settings for servers with an SMTP connection.', 'N'),
('System', 'mailerSMTPHost', 'SMTP Host', 'Set the hostname of the mail server.', ''),
('System', 'mailerSMTPPort', 'SMTP Port', 'Set the SMTP port number - likely to be 25, 465 or 587.', '25'),
('System', 'mailerSMTPUsername', 'SMTP Username', 'Username to use for SMTP authentication. Leave blank for no authentication.', ''),
('System', 'mailerSMTPPassword', 'SMTP Password', 'Password to use for SMTP authentication. Leave blank for no authentication.', ''),
('System', 'mainMenuCategoryOrder', 'Main Menu Category Order', 'A comma separated list of module categories in display order.', 'Admin,Assess,Learn,People,Other'),
('Attendance', 'attendanceReasons', 'Attendance Reasons', 'Comma-separated list of reasons which are available when taking attendance.', 'Pending,Education,Family,Medical,Other'),
('Attendance', 'attendanceMedicalReasons', 'Medical Reasons', 'Comma-separated list of allowable medical reasons.', 'Medical'),
('Attendance', 'attendanceEnableMedicalTracking', 'Enable Symptom Tracking', 'Attach a symptom report to attendance logs with a medical reason.', 'N'),
('Students', 'medicalIllnessSymptoms', 'Predefined Illness Symptoms', 'Comma-separated list of illness symptoms.', 'Fever,Cough,Cold,Vomiting,Diarrhea'),
('Staff Application Form', 'staffApplicationFormPublicApplications', 'Public Applications?', 'If yes, members of the public can submit staff applications', 'Y'),
('Individual Needs', 'targetsTemplate', 'Targets Template', 'An HTML template to be used in the targets field.', NULL),
('Individual Needs', 'teachingStrategiesTemplate', 'Teaching Strategies Template', 'An HTML template to be used in the teaching strategies field.', NULL),
('Individual Needs', 'notesReviewTemplate', 'Notes & Review Template', 'An HTML template to be used in the notes and review field.', NULL),
('Attendance', 'attendanceCLINotifyByRollGroup', 'Enable Notifications by Roll Group', '', 'Y'),
('Attendance', 'attendanceCLINotifyByClass', 'Enable Notifications by Class', '', 'Y'),
('Attendance', 'attendanceCLIAdditionalUsers', 'Additional Users to Notify', 'Send the school-wide daily attendance report to additional users. Restricted to roles with permission to access Roll Groups Not Registered or Classes Not Registered.', ''),
('Students', 'noteCreationNotification', 'Note Creation Notification', 'Determines who to notify when a new student note is created.', 'Tutors'),
('Finance', 'invoiceeNameStyle', 'Invoicee Name Style', 'Determines how invoicee name appears on invoices and receipts.', 'Surname, Preferred Name'),
('Planner', 'shareUnitOutline', 'Share Unit Outline', 'Allow users who do not have access to the unit planner to see Unit Outlines via the lesson planner?', 'N'),
('Attendance', 'studentSelfRegistrationIPAddresses', 'Student Self Registration IP Addresses', 'Comma-separated list of IP addresses within which students can self register.', ''),
('Application Form', 'internalDocuments', 'Internal Documents', 'Comma-separated list of documents for internal upload and use.', ''),
('Attendance', 'countClassAsSchool', 'Count Class Attendance as School Attendance', 'Should attendance from the class context be used to prefill and inform school attendance?', 'N'),
('Attendance', 'defaultRollGroupAttendanceType', 'Default Roll Group Attendance Type', 'The default selection for attendance type when taking Roll Group attendance', 'Present'),
('Attendance', 'defaultClassAttendanceType', 'Default Class Attendance Type', 'The default selection for attendance type when taking Class attendance', 'Present'),
('Students', 'academicAlertLowThreshold', 'Low Academic Alert Threshold', 'The number of Markbook concerns needed in the past 60 days to raise a low level academic alert on a student.', '3'),
('Students', 'academicAlertMediumThreshold', 'Medium Academic Alert Threshold', 'The number of Markbook concerns needed in the past 60 days to raise a medium level academic alert on a student.', '5'),
('Students', 'academicAlertHighThreshold', 'High Academic Alert Threshold', 'The number of Markbook concerns needed in the past 60 days to raise a high level academic alert on a student.', '9'),
('Students', 'behaviourAlertLowThreshold', 'Low Behaviour Alert Threshold', 'The number of Behaviour concerns needed in the past 60 days to raise a low level alert on a student.', '3'),
('Students', 'behaviourAlertMediumThreshold', 'Medium Behaviour Alert Threshold', 'The number of Behaviour concerns needed in the past 60 days to raise a medium level alert on a student.', '5'),
('Students', 'behaviourAlertHighThreshold', 'High Behaviour Alert Threshold', 'The number of Behaviour concerns needed in the past 60 days to raise a high level alert on a student.', '9'),
('Markbook', 'enableDisplayCumulativeMarks', 'Enable Display Cumulative Marks', 'Should cumulative marks be displayed on the View Markbook page for Students and Parents and in Student Profiles?', 'N'),
('Application Form', 'scholarshipOptionsActive', 'Scholarship Options Active', 'Should the Scholarship Options section be turned on?', 'Y'),
('Application Form', 'paymentOptionsActive', 'Payment Options Active', 'Should the Payment section be turned on?', 'Y'),
('Application Form', 'senOptionsActive', 'Special Education Needs Active', 'Should the Special Education Needs section be turned on?', 'Y'),
('Timetable Admin', 'autoEnrolCourses', 'Auto-Enrol Courses Default', 'Should auto-enrolment of new students into courses be turned on or off by default?', 'N'),
('Application Form', 'availableYearsOfEntry', 'Available Years of Entry', 'Which school years should be available to apply to?', ''),
('Application Form', 'enableLimitedYearsOfEntry', 'Enable Limited Years of Entry', 'If yes, applicants choices for Year of Entry can be limited to specific school years.', 'N'),
('User Admin', 'uniqueEmailAddress', 'Unique Email Address', 'Are primary email addresses required to be unique?', 'N'),
('Planner', 'parentWeeklyEmailSummaryIncludeMarkbook', 'Parent Weekly Email Summary Include Markbook', 'Should Markbook information be included in the weekly planner email summary that goes out to parents?', 'N'),
('System', 'nameFormatStaffFormal', 'Formal Name Format', '', '[title] [preferredName:1]. [surname]'),
('System', 'nameFormatStaffFormalReversed', 'Formal Name Reversed', '', '[title] [surname], [preferredName:1].'),
('System', 'nameFormatStaffInformal', 'Informal Name Format', '', '[preferredName] [surname]'),
('System', 'nameFormatStaffInformalReversed', 'Informal Name Reversed', '', '[surname], [preferredName]'),
('Attendance', 'selfRegistrationRedirect', 'Self Registration Redirect', 'Should self registration redirect to Message Wall?', 'N'),
('Data Updater', 'cutoffDate', 'Cutoff Date', 'Earliest acceptable date when checking if data updates are required.', ''),
('Data Updater', 'redirectByRoleCategory', 'Data Updater Redirect', 'Which types of users should be redirected to the Data Updater if updates are required.', 'Parent'),
('Data Updater', 'requiredUpdates', 'Required Updates?', 'Should the data updater highlight updates that are required?', 'N'),
('Data Updater', 'requiredUpdatesByType', 'Required Update Types', 'Which type of data updates should be required.', 'Personal,Family'),
('Markbook', 'enableModifiedAssessment', 'Enable Modified Assessment', 'Allows teachers to specify \"Modified Assessment\" for students with individual needs.', 'N'),
('Messenger', 'messageBcc', 'Message Bcc', 'Comma-separated list of recipients to bcc all messenger emails to.', ''),
('System', 'organisationBackground', 'Background', 'Relative path to background image. Overrides theme background.', '\\uploads\\2020\\03\\org_bg_1_5e8296c9d41fb.jpeg'),
('Messenger', 'smsGateway', 'SMS Gateway', '', ''),
('Messenger', 'smsSenderID', 'SMS Sender ID', 'The sender name or phone number. Depends on the gateway used.', ''),
('System Admin', 'exportDefaultFileType', 'Default Export File Type', '', 'Excel2007'),
('System', 'mailerSMTPSecure', 'SMTP Encryption', 'Automatically sets the encryption based on the port, otherwise select one manually.', 'auto'),
('Staff', 'substituteTypes', 'Substitute Types', 'A comma-separated list.', 'Internal Substitute,External Substitute'),
('Staff', 'urgencyThreshold', 'Urgency Threshold', 'Notifications in this time-span are sent immediately, day or night.', '3'),
('Staff', 'urgentNotifications', 'Urgent Notifications', 'If enabled, urgent notifications will be sent by SMS as well as email.', 'N'),
('Staff', 'absenceApprovers', 'Absence Approvers', 'Users who can approve staff absences. Leave this blank if approval is not used.', ''),
('Staff', 'absenceFullDayThreshold', 'Full Day Absence', 'The minumum number of hours for an absence to count as a full day (1.0)', '6.0'),
('Staff', 'absenceHalfDayThreshold', 'Half Day Absence', 'The minumum number of hours for an absence to count as a half day (.5). Absences less than this count as 0', '2.0'),
('Staff', 'absenceNotificationGroups', 'Notification Groups', 'Which messenger groups can staff members send absence notifications to?', ''),
('Attendance', 'crossFillClasses', 'Cross-Fill Classes', 'Should classes prefill with data from other classes?', 'N');";
    }

}