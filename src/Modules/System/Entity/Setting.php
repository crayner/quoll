<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 25/11/2018
 * Time: 10:00
 */
namespace App\Modules\System\Entity;

use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Setting
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\SettingRepository")
 * @ORM\Table(name="Setting",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="scope_display",columns={"scope","name_display"}),
 *     @ORM\UniqueConstraint(name="scope_name",columns={"scope","name"})})
 * @UniqueEntity({"name","scope"})
 * @UniqueEntity({"nameDisplay","scope"})
 */
class Setting extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $scope;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=60)
     */
    private $nameDisplay;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $value;

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
     * @return Setting
     */
    public function setId(?string $id): Setting
    {
        $this->id = $id;
        return $this;
    }

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

    /**
     * create
     * @return array|string[]
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Setting` (
                    `id` CHAR(36) NOT NULL,
                    `scope` CHAR(50) NOT NULL,
                    `name` CHAR(50) NOT NULL,
                    `name_display` CHAR(60) NOT NULL,
                    `description` CHAR(191) DEFAULT NULL,
                    `value` longtext DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `scope_name` (`scope`,`name`),
                    UNIQUE KEY `scope_display` (`scope`,`name_display`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public function coreData(): array
    {
        return Yaml::parse("
-
  scope: 'System'
  name: 'emailLink'
  nameDisplay: 'Link To Email'
  description: 'The link that points to the school''s email system'
-
  scope: 'Application Form'
  name: 'notificationStudentMessage'
  nameDisplay: 'Student Notification Message'
  description: 'A custom message to add to the standard email to students on acceptance.'
-
  scope: 'Finance'
  name: 'invoiceNumber'
  nameDisplay: 'Invoice Number Style'
  description: 'How should invoice numbers be constructed?'
  value: 'Invoice ID'
-
  scope: 'People'
  name: 'departureReasons'
  nameDisplay: 'Departure Reasons'
  description: 'A list of reasons for departure from school. If blank, user can enter any text.'
-
  scope: 'Mark Book'
  name: 'personalisedWarnings'
  nameDisplay: 'Personalised Warnings'
  description: 'Should mark book warnings be based on personal targets, if they are available?'
  value: 'Y'
-
  scope: 'System'
  name: 'webLink'
  nameDisplay: 'Link To Web'
  description: 'The link that points to the school''s website'
-
  scope: 'Activities'
  name: 'disableExternalProviderSignup'
  nameDisplay: 'Disable External Provider Signup'
  description: 'Should we turn off the option to sign up for activities provided by an outside agency?'
  value: 'N'
-
  scope: 'Activities'
  name: 'hideExternalProviderCost'
  nameDisplay: 'Hide External Provider Cost'
  description: 'Should we hide the cost of activities provided by an outside agency from the Activities View?'
  value: 'N'
-
  scope: 'Application Form'
  name: 'studentDefaultEmail'
  nameDisplay: 'Student Default Email'
  description: 'Set default email for students on acceptance, using [username] to insert username.'
-
  scope: 'Application Form'
  name: 'studentDefaultWebsite'
  nameDisplay: 'Student Default Website'
  description: 'Set default website for students on acceptance, using [username] to insert username.'
-
  scope: 'School Admin'
  name: 'studentAgreementOptions'
  nameDisplay: 'Student Agreement Options'
  description: 'A list of agreements that students might be asked to sign in school (e.g. ICT Policy).'
-
  scope: 'Mark Book'
  name: 'attainmentAlternativeName'
  nameDisplay: 'Attainment Alternative Name'
  description: 'A name to use instead of ''Attainment'' in the first grade column of the mark book.'
-
  scope: 'Mark Book'
  name: 'effortAlternativeName'
  nameDisplay: 'Effort Alternative Name'
  description: 'A name to use instead of ''Effort'' in the second grade column of the mark book.'
-
  scope: 'Mark Book'
  name: 'attainmentAlternativeNameAbrev'
  nameDisplay: 'Attainment Alternative Name Abbreviation'
  description: 'A short name to use instead of ''Attainment'' in the first grade column of the mark book.'
-
  scope: 'Mark Book'
  name: 'effortAlternativeNameAbrev'
  nameDisplay: 'Effort Alternative Name Abbreviation'
  description: 'A short name to use instead of ''Effort'' in the second grade column of the mark book.'
-
  scope: 'Planner'
  name: 'parentWeeklyEmailSummaryIncludeBehaviour'
  nameDisplay: 'Parent Weekly Email Summary Include Behaviour'
  description: 'Should behaviour information be included in the weekly planner email summary that goes out to parents?'
  value: 'Y'
-
  scope: 'System'
  name: 'defaultAssessmentScale'
  nameDisplay: 'Default Assessment Scale'
  description: 'This is the scale used as a default where assessment scales need to be selected.'
  value: 7
-
  scope: 'Finance'
  name: 'financeOnlinePaymentEnabled'
  nameDisplay: 'Enable Online Payment'
  description: 'Should invoices be payable online, via an encrypted link in the invoice? Requires correctly configured payment gateway in System Settings.'
  value: 'N'
-
  scope: 'Finance'
  name: 'financeOnlinePaymentThreshold'
  nameDisplay: 'Online Payment Threshold'
  description: 'If invoices are payable online, what is the maximum payment allowed? Useful for controlling payment fees. No value means unlimited.'
-
  scope: 'Departments'
  name: 'makeDepartmentsPublic'
  nameDisplay: 'Make Departments Public'
  description: 'Should department information be made available to the public, via the Gibbon homepage?'
  value: 'N'
-
  scope: 'System'
  name: 'sessionDuration'
  nameDisplay: 'Session Duration'
  description: 'Time, in seconds, before system logs a user out. Should be less than PHP''s session.gc_maxlifetime option.'
  value: 900
-
  scope: 'Planner'
  name: 'makeUnitsPublic'
  nameDisplay: 'Make Units Public'
  description: 'Enables a public listing of units, with teachers able to opt in to share units.'
  value: 'Y'
-
  scope: 'Messenger'
  name: 'messageBubbleWidthType'
  nameDisplay: 'Message Bubble Width Type'
  description: 'Should the message bubble be regular or wide?'
  value: 'Regular'
-
  scope: 'Messenger'
  name: 'messageBubbleBGColor'
  nameDisplay: 'Message Bubble Background Color'
  description: 'Message bubble background color in RGBA (e.g. 100,100,100,0.50). If blank, theme default will be used.'
-
  scope: 'Messenger'
  name: 'messageBubbleAutoHide'
  nameDisplay: 'Message Bubble Auto Hide'
  description: 'Should message bubble fade out automatically?'
  value: 'Y'
-
  scope: 'Students'
  name: 'enableStudentNotes'
  nameDisplay: 'Enable Student Notes'
  description: 'Should student notes be turned on?'
  value: 'Y'
-
  scope: 'Finance'
  name: 'budgetCategories'
  nameDisplay: 'Budget Categories'
  description: 'A list of budget categories.'
  value: 'Academic,Administration,Capital'
-
  scope: 'Finance'
  name: 'expenseApprovalType'
  nameDisplay: 'Expense Approval Type'
  description: 'How should expense approval be dealt with?'
  value: 'One Of'
-
  scope: 'Finance'
  name: 'budgetLevelExpenseApproval'
  nameDisplay: 'Budget Level Expense Approval'
  description: 'Should approval from a budget member with Full access be required?'
  value: 'Y'
-
  scope: 'Finance'
  name: 'expenseRequestTemplate'
  nameDisplay: 'Expense Request Template'
  description: 'An HTML template to be used in the description field of expense requests.'
-
  scope: 'Finance'
  name: 'purchasingOfficer'
  nameDisplay: 'Purchasing Officer'
  description: 'User responsible for purchasing for the school.'
-
  scope: 'Finance'
  name: 'reimbursementOfficer'
  nameDisplay: 'Reimbursement Officer'
  description: 'User responsible for reimbursing expenses.'
-
  scope: 'Messenger'
  name: 'enableHomeScreenWidget'
  nameDisplay: 'Enable Home Screen Widget'
  description: 'Adds a Message Wall widget to the home page, hihglighting current messages.'
  value: 'N'
-
  scope: 'People'
  name: 'enablePublicRegistration'
  nameDisplay: 'Enable Public Registration'
  description: 'Allows members of the public to register to use the system.'
  value: 'Y'
-
  scope: 'People'
  name: 'publicRegistrationMinimumAge'
  nameDisplay: 'Public Registration Minimum Age'
  description: 'The minimum age, in years, permitted to register.'
  value: 13
-
  scope: 'People'
  name: 'publicRegistrationDefaultStatus'
  nameDisplay: 'Public Registration Default Status'
  description: 'Should new users be ''Full'' or ''Pending Approval''?'
  value: 'Pending Approval'
-
  scope: 'People'
  name: 'publicRegistrationDefaultRole'
  nameDisplay: 'Public Registration Default Role'
  description: 'System role to be assigned to registering members of the public.'
  value: 3
-
  scope: 'System'
  name: 'organisationLogo'
  nameDisplay: 'Logo'
  description: 'Relative path to site logo (400 x 100px)'
-
  scope: 'People'
  name: 'publicRegistrationIntro'
  nameDisplay: 'Public Registration Introductory Text'
  description: 'HTML text that will appear above the public registration form.'
-
  scope: 'People'
  name: 'publicRegistrationPrivacyStatement'
  nameDisplay: 'Public Registration Privacy Statement'
  description: 'HTML text that will appear above the Submit button, explaining privacy policy.'
  value: 'By registering for this site you are giving permission for your personal data to be used and shared within this organisation and its websites. We will not share your personal data outside our organisation.'
-
  scope: 'People'
  name: 'publicRegistrationAgreement'
  nameDisplay: 'Public Registration Agreement'
  description: 'Agreement that user must confirm before joining. Blank for no agreement.'
  value: 'In joining this site, and checking the box below, I agree to act lawfully, ethically and with respect for others. I agree to use this site for learning purposes only, and understand that access may be withdrawn at any time, at the discretion of the site''s administrators.'
-
  scope: 'People'
  name: 'publicRegistrationPostscript'
  nameDisplay: 'Public Registration Postscript'
  description: 'HTML text that will appear underneath the public registration form.'
-
  scope: 'Behaviour'
  name: 'enableDescriptors'
  nameDisplay: 'Enable Descriptors'
  description: 'Setting to No reduces complexity of behaviour tracking.'
  value: 'Y'
-
  scope: 'Behaviour'
  name: 'enableLevels'
  nameDisplay: 'Enable Levels'
  description: 'Setting to No reduces complexity of behaviour tracking.'
  value: 'Y'
-
  scope: 'Formal Assessment'
  name: 'internalAssessmentTypes'
  nameDisplay: 'Internal Assessment Types'
  description: 'A list of types to make available in Internal Assessments.'
  value: 'Expected Grade,Predicted Grade,Target Grade'
-
  scope: 'System Admin'
  name: 'customAlarmSound'
  nameDisplay: 'Custom Alarm Sound'
  description: 'A custom alarm sound file.'
-
  scope: 'School Admin'
  name: 'facilityTypes'
  nameDisplay: 'FacilityTypes'
  description: 'A list of types for facilities.'
  value: 'Classroom,Hall,Laboratory,Library,Office,Outdoor,Performance,Staffroom,Storage,Study,Undercover,Other'
-
  scope: 'Finance'
  name: 'allowExpenseAdd'
  nameDisplay: 'Allow Expense Add'
  description: 'Allows privileged users to add expenses without going through request process.'
  value: 'Y'
-
  scope: 'System'
  name: 'calendarFeed'
  nameDisplay: 'School Google Calendar ID'
  description: 'Google Calendar ID for your school calendar. Only enables timetable integration when logging in via Google.'
  value: 'craig@craigrayner.com'
-
  scope: 'System'
  name: 'organisationAdministrator'
  nameDisplay: 'System Administrator'
  description: 'The staff member who receives notifications for system events.'
  value: 1
-
  scope: 'System'
  name: 'organisationDBA'
  nameDisplay: 'Database Administrator'
  description: 'The staff member who receives notifications for data events.'
  value: 1
-
  scope: 'System'
  name: 'organisationAdmissions'
  nameDisplay: 'Admissions Administrator'
  description: 'The staff member who receives notifications for admissions events.'
  value: 1
-
  scope: 'Finance'
  name: 'hideItemisation'
  nameDisplay: 'Hide Itemisation'
  description: 'Hide fee and payment details in receipts?'
  value: 'N'
-
  scope: 'Application Form'
  name: 'autoHouseAssign'
  nameDisplay: 'Auto House Assign'
  description: 'Attempt to automatically place student in a house?'
  value: 'N'
-
  scope: 'Tracking'
  name: 'externalAssessmentDataPoints'
  nameDisplay: 'External Assessment Data Points'
  description: 'Stores the external assessment choices for data points output in tracking.'
  value: 'a:7:{i:0;a:3:{s:10:''assessment'';s:1:''1'';s:8:''category'';s:8:''1_Scores'';s:13:''yearGroupList'';a:3:{i:0;s:1:''1'';i:1;s:1:''2'';i:2;s:1:''3'';}}i:1;a:3:{s:10:''assessment'';s:1:''1'';s:8:''category'';s:19:''2_KS3 Target Grades'';s:13:''yearGroupList'';a:3:{i:0;s:1:''1'';i:1;s:1:''2'';i:2;s:1:''3'';}}i:2;a:3:{s:10:''assessment'';s:1:''1'';s:8:''category'';s:20:''3_GCSE Target Grades'';s:13:''yearGroupList'';a:2:{i:0;s:1:''4'';i:1;s:1:''5'';}}i:3;a:3:{s:10:''assessment'';s:1:''2'';s:8:''category'';s:14:''2_Target Grade'';s:13:''yearGroupList'';a:2:{i:0;s:1:''4'';i:1;s:1:''5'';}}i:4;a:3:{s:10:''assessment'';s:1:''2'';s:8:''category'';s:13:''1_Final Grade'';s:13:''yearGroupList'';a:2:{i:0;s:1:''4'';i:1;s:1:''5'';}}i:5;a:3:{s:10:''assessment'';s:1:''3'';s:8:''category'';s:14:''2_Target Grade'';s:13:''yearGroupList'';a:2:{i:0;s:1:''6'';i:1;s:1:''7'';}}i:6;a:3:{s:10:''assessment'';s:1:''3'';s:8:''category'';s:13:''1_Final Grade'';s:13:''yearGroupList'';a:2:{i:0;s:1:''6'';i:1;s:1:''7'';}}}'
-
  scope: 'Tracking'
  name: 'internalAssessmentDataPoints'
  nameDisplay: 'Internal Assessment Data Points'
  description: 'Stores the internal assessment choices for data points output in tracking.'
  value: 'a:3:{i:0;a:2:{s:4:''type'';s:14:''Expected Grade'';s:13:''yearGroupList'';a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:1;a:2:{s:4:''type'';s:15:''Predicted Grade'';s:13:''yearGroupList'';a:2:{i:0;i:4;i:1;i:5;}}i:2;a:2:{s:4:''type'';s:12:''Target Grade'';s:13:''yearGroupList'';a:2:{i:0;i:6;i:1;i:7;}}}'
-
  scope: 'Behaviour'
  name: 'enableBehaviourLetters'
  nameDisplay: 'Enable Behaviour Letters'
  description: 'Should automated behaviour letter functionality be enabled?'
  value: 'N'
-
  scope: 'Behaviour'
  name: 'behaviourLettersLetter1Count'
  nameDisplay: 'Letter 1 Count'
  description: 'After how many negative records should letter 1 be sent?'
  value: 3
-
  scope: 'Behaviour'
  name: 'behaviourLettersLetter1Text'
  nameDisplay: 'Letter 1 Text'
  description: 'The contents of letter 1, as HTML.'
  value: 'Dear Parent/Guardian,<br/><br/>This letter has been automatically generated to alert you to the fact that your child, [studentName], has reached [behaviourCount] negative behaviour incidents. Please see the list below for the details of these incidents:<br/><br/>[behaviourRecord]<br/><br/>This letter represents the first communication in a sequence of 3 potential alerts, each of which is more critical than the last.<br/><br/>If you would like more information on this matter, please contact your child''s tutor.'
-
  scope: 'Activities'
  name: 'access'
  nameDisplay: 'Access'
  description: 'System-wide access control'
  value: 'Register'
-
  scope: 'Behaviour'
  name: 'behaviourLettersLetter2Count'
  nameDisplay: 'Letter 2 Count'
  description: 'After how many negative records should letter 2 be sent?'
  value: 6
-
  scope: 'Behaviour'
  name: 'behaviourLettersLetter2Text'
  nameDisplay: 'Letter 2 Text'
  description: 'The contents of letter 2, as HTML.'
  value: 'Dear Parent/Guardian,<br/><br/>This letter has been automatically generated to alert you to the fact that your child, [studentName], has reached [behaviourCount] negative behaviour incidents. Please see the list below for the details of these incidents:<br/><br/>[behaviourRecord]<br/><br/>This letter represents the second communication in a sequence of 3 potential alerts, each of which is more critical than the last.<br/><br/>If you would like more information on this matter, please contact your child''s tutor.'
-
  scope: 'Behaviour'
  name: 'behaviourLettersLetter3Count'
  nameDisplay: 'Letter 3 Count'
  description: 'After how many negative records should letter 3 be sent?'
  value: 9
-
  scope: 'Behaviour'
  name: 'behaviourLettersLetter3Text'
  nameDisplay: 'Letter 3 Text'
  description: 'The contents of letter 3, as HTML.'
  value: 'Dear Parent/Guardian,<br/><br/>This letter has been automatically generated to alert you to the fact that your child, [studentName], has reached [behaviourCount] negative behaviour incidents. Please see the list below for the details of these incidents:<br/><br/>[behaviourRecord]<br/><br/>This letter represents the final communication in a sequence of 3 potential alerts, each of which is more critical than the last.<br/><br/>If you would like more information on this matter, please contact your child''s tutor.'
-
  scope: 'Mark Book'
  name: 'enableColumnWeighting'
  nameDisplay: 'Enable Column Weighting'
  description: 'Should column weighting and total scores be enabled in the Mark book?'
  value: 'N'
-
  scope: 'System'
  name: 'firstDayOfTheWeek'
  nameDisplay: 'First Day Of The Week'
  description: 'On which day should the week begin?'
  value: 'Sunday'
-
  scope: 'Application Form'
  name: 'usernameFormat'
  nameDisplay: 'Username Format'
  description: 'How should usernames be formatted? Choose from [preferredName], [preferredNameInitial], [surname].'
  value: '[preferredNameInitial][surname]'
-
  scope: 'Staff'
  name: 'jobOpeningDescriptionTemplate'
  nameDisplay: 'Job Opening Description Template'
  description: 'Default HTML contents for the Job Opening Description field.'
  value: >-
        <table style=''width: 100%''>
        <tr>
        <td colspan=2 style=''vertical-align: top''>
        <span style=''text-decoration: underline; font-weight: bold''>Job Description</span><br/>
        <br/>
        </td>
        </tr>
        <tr>
        <td style=''width: 50%; vertical-align: top''>
        <span style=''text-decoration: underline; font-weight: bold''>Responsibilities</span><br/>
        <ul style=''margin-top:0px''>
        <li></li>
        <li></li>
        </ul>
        </td>
        <td style=''width: 50%; vertical-align: top''>
        <span style=''text-decoration: underline; font-weight: bold''>Required Skills/Characteristics</span><br/>
        <ul style=''margin-top:0px''>
        <li></li>
        <li></li>
        </ul>
        </td>
        </tr>
        <tr>
        <td style=''width: 50%; vertical-align: top''>
        <span style=''text-decoration: underline; font-weight: bold''>Remuneration</span><br/>
        <ul style=''margin-top:0px''>
        <li></li>
        <li></li>
        </ul>
        </td>
        <td style=''width: 50%; vertical-align: top''>
        <span style=''text-decoration: underline; font-weight: bold''>Other Details </span><br/>
        <ul style=''margin-top: 0px''>
        <li></li>
        <li></li>
        </ul>
        </td>
        </tr>
        </table>
-
  scope: 'Staff'
  name: 'staffApplicationFormIntroduction'
  nameDisplay: 'Introduction'
  description: 'Information to display before the form'
-
  scope: 'Staff'
  name: 'staffApplicationFormPostscript'
  nameDisplay: 'Postscript'
  description: 'Information to display at the end of the form'
-
  scope: 'Activities'
  name: 'payment'
  nameDisplay: 'Payment'
  description: 'Payment system'
  value: 'Per Activity'
-
  scope: 'Staff'
  name: 'staffApplicationFormAgreement'
  nameDisplay: 'Agreement'
  description: 'Without this text, which is displayed above the agreement, users will not be asked to agree to anything'
  value: 'In submitting this form, I confirm that all information provided above is accurate and complete to the best of my knowledge.'
-
  scope: 'Staff'
  name: 'staffApplicationFormMilestones'
  nameDisplay: 'Milestones'
  description: 'A list of the major steps in the application process. Applicants can be tracked through the various stages.'
  value: 'Short List, First Interview, Second Interview, Offer Made, Offer Accepted, Contact Issued, Contact Signed'
-
  scope: 'Staff'
  name: 'staffApplicationFormRequiredDocuments'
  nameDisplay: 'Required Documents'
  description: 'A list of documents which must be submitted electronically with the application form.'
  value: 'Curriculum Vitae'
-
  scope: 'Staff'
  name: 'staffApplicationFormRequiredDocumentsCompulsory'
  nameDisplay: 'Required Documents Compulsory?'
  description: 'Are the required documents compulsory?'
  value: 'Y'
-
  scope: 'Staff'
  name: 'staffApplicationFormRequiredDocumentsText'
  nameDisplay: 'Required Documents Text'
  description: 'Explanatory text to appear with the required documents?'
  value: 'Please submit the following document(s) to ensure your application can be processed without delay.'
-
  scope: 'Staff'
  name: 'staffApplicationFormNotificationDefault'
  nameDisplay: 'Notification Default'
  description: 'Should acceptance email be turned on or off by default.'
  value: 'Y'
-
  scope: 'Staff'
  name: 'staffApplicationFormNotificationMessage'
  nameDisplay: 'Notification Message'
  description: 'A custom message to add to the standard email on acceptance.'
-
  scope: 'Staff'
  name: 'staffApplicationFormDefaultEmail'
  nameDisplay: 'Default Email'
  description: 'Set default email on acceptance, using [username] to insert username.'
-
  scope: 'Staff'
  name: 'staffApplicationFormDefaultWebsite'
  nameDisplay: 'Default Website'
  description: 'Set default website on acceptance, using [username] to insert username.'
-
  scope: 'Staff'
  name: 'staffApplicationFormUsernameFormat'
  nameDisplay: 'Username Format'
  description: 'How should usernames be formated? Choose from [preferredName], [preferredNameInitial], [surname].'
  value: '[preferredNameInitial].[surname]'
-
  scope: 'Activities'
  name: 'enrolmentType'
  nameDisplay: 'Enrolment Type'
  description: 'Enrolment process type'
  value: 'Competitive'
-
  scope: 'System'
  name: 'organisationHR'
  nameDisplay: 'Human Resources Administrator'
  description: 'The staff member who receives notifications for staffing events.'
  value: 1
-
  scope: 'Staff'
  name: 'staffApplicationFormQuestions'
  nameDisplay: 'Application Questions'
  description: 'HTML text that will appear as questions for the applicant to answer.'
  value: '<span style=''text-decoration: underline; font-weight: bold''>Why are you applying for this role?</span><br/><p></p>'
-
  scope: 'Staff'
  name: 'salaryScalePositions'
  nameDisplay: 'Salary Scale Positions'
  description: 'A list of salary scale positions, from lowest to highest.'
  value: '1,2,3,4,5,6,7,8,9,10'
-
  scope: 'Staff'
  name: 'responsibilityPosts'
  nameDisplay: 'Responsibility Posts'
  description: 'A list of posts carrying extra responsibilities.'
-
  scope: 'Students'
  name: 'applicationFormSENText'
  nameDisplay: 'Application Form SEN Text'
  description: 'Text to appear with the Special Educational Needs section of the student application form.'
  value: 'Please indicate whether or not your child has any known, or suspected, special educational needs, or whether they have been assessed for any such needs in the past. Provide any comments or information concerning your child''s development that may be relevant to your child''s performance in the classroom or elsewhere? Incorrect or withheld information may affect continued enrolment.'
-
  scope: 'Students'
  name: 'applicationFormRefereeLink'
  nameDisplay: 'Application Form Referee Link'
  description: 'Link to an external form that will be emailed to a referee of the applicant''s choosing.'
-
  scope: 'People'
  name: 'religions'
  nameDisplay: 'Religions'
  description: 'A list of religions available in system'
  value: ''',Nonreligious/Agnostic/Atheist,Buddhism,Christianity,Hinduism,Islam,Judaism,Other'
-
  scope: 'Staff'
  name: 'applicationFormRefereeLink'
  nameDisplay: 'Application Form Referee Link'
  description: 'Link to an external form that will be emailed to a referee of the applicant''s choosing.'
-
  scope: 'Mark Book'
  name: 'enableRawAttainment'
  nameDisplay: 'Enable Raw Attainment Marks'
  description: 'Should recording of raw marks be enabled in the Mark book?'
  value: 'N'
-
  scope: 'Mark Book'
  name: 'enableGroupByTerm'
  nameDisplay: 'Group Columns by Term'
  description: 'Should columns and total scores be grouped by term?'
  value: 'N'
-
  scope: 'Activities'
  name: 'backupChoice'
  nameDisplay: 'Backup Choice'
  description: 'Allow students to choose a backup, in case enrolled activity is full.'
  value: 'Y'
-
  scope: 'Mark Book'
  name: 'enableEffort'
  nameDisplay: 'Enable Effort'
  description: 'Should columns have the Effort section enabled?'
  value: 'Y'
-
  scope: 'Mark Book'
  name: 'enableRubrics'
  nameDisplay: 'Enable Rubrics'
  description: 'Should columns have Rubrics section enabled?'
  value: 'Y'
-
  scope: 'School Admin'
  name: 'staffDashboardDefaultTab'
  nameDisplay: 'Staff Dashboard Default Tab'
  description: 'The default landing tab for the staff dashboard.'
-
  scope: 'School Admin'
  name: 'studentDashboardDefaultTab'
  nameDisplay: 'Student Dashboard Default Tab'
  description: 'The default landing tab for the student dashboard.'
-
  scope: 'School Admin'
  name: 'parentDashboardDefaultTab'
  nameDisplay: 'Parent Dashboard Default Tab'
  description: 'The default landing tab for the parent dashboard.'
  value: 'Timetable'
-
  scope: 'System'
  name: 'organisationName'
  nameDisplay: 'Organisation Name'
  description: ''
-
  scope: 'Activities'
  name: 'activityTypes'
  nameDisplay: 'Activity Types'
  description: 'Comma-seperated list of the different activity types available in school. Leave blank to disable this feature.'
  value: 'Creativity,Action,Service'
-
  scope: 'System'
  name: 'mainMenuCategoryOrder'
  nameDisplay: 'Main Menu Category Order'
  description: 'A list of module categories in display order.'
  value: 'Admin,Assess,Learn,People,Other'
-
  scope: 'Attendance'
  name: 'attendanceReasons'
  nameDisplay: 'Attendance Reasons'
  description: 'A list of reasons which are available when taking attendance.'
  value: 'Pending,Education,Family,Medical,Other'
-
  scope: 'Attendance'
  name: 'attendanceMedicalReasons'
  nameDisplay: 'Medical Reasons'
  description: 'A list of allowable medical reasons.'
  value: 'Medical'
-
  scope: 'Attendance'
  name: 'attendanceEnableMedicalTracking'
  nameDisplay: 'Enable Symptom Tracking'
  description: 'Attach a symptom report to attendance logs with a medical reason.'
  value: 'N'
-
  scope: 'Students'
  name: 'medicalIllnessSymptoms'
  nameDisplay: 'Predefined Illness Symptoms'
  description: 'A list of illness symptoms.'
  value: 'Fever,Cough,Cold,Vomiting,Diarrhea'
-
  scope: 'Staff Application Form'
  name: 'staffApplicationFormPublicApplications'
  nameDisplay: 'Public Applications?'
  description: 'If yes, members of the public can submit staff applications'
  value: 'N'
-
  scope: 'Individual Needs'
  name: 'targetsTemplate'
  nameDisplay: 'Targets Template'
  description: 'An HTML template to be used in the targets field.'
-
  scope: 'Individual Needs'
  name: 'teachingStrategiesTemplate'
  nameDisplay: 'Teaching Strategies Template'
  description: 'An HTML template to be used in the teaching strategies field.'
-
  scope: 'Individual Needs'
  name: 'notesReviewTemplate'
  nameDisplay: 'Notes & Review Template'
  description: 'An HTML template to be used in the notes and review field.'
-
  scope: 'Attendance'
  name: 'attendanceCLINotifyByRollGroup'
  nameDisplay: 'Enable Notifications by Roll Group'
  value: 'Y'
-
  scope: 'Application Form'
  name: 'introduction'
  nameDisplay: 'Introduction'
  description: 'Information to display before the form'
-
  scope: 'Attendance'
  name: 'attendanceCLINotifyByClass'
  nameDisplay: 'Enable Notifications by Class'
  value: 'Y'
-
  scope: 'Attendance'
  name: 'attendanceCLIAdditionalUsers'
  nameDisplay: 'Additional Users to Notify'
  description: 'Send the school-wide daily attendance report to additional users. Restricted to roles with permission to access Roll Groups Not Registered or Classes Not Registered.'
-
  scope: 'Students'
  name: 'noteCreationNotification'
  nameDisplay: 'Note Creation Notification'
  description: 'Determines who to notify when a new student note is created.'
  value: 'Tutors'
-
  scope: 'Finance'
  name: 'invoiceeNameStyle'
  nameDisplay: 'Invoicee Name Style'
  description: 'Determines how invoicee name appears on invoices and receipts.'
  value: 'Surname, Preferred Name'
-
  scope: 'Planner'
  name: 'shareUnitOutline'
  nameDisplay: 'Share Unit Outline'
  description: 'Allow users who do not have access to the unit planner to see Unit Outlines via the lesson planner?'
  value: 'N'
-
  scope: 'Attendance'
  name: 'studentSelfRegistrationIPAddresses'
  nameDisplay: 'Student Self Registration IP Addresses'
  description: 'A list of IP addresses within which students can self register.'
-
  scope: 'Application Form'
  name: 'internalDocuments'
  nameDisplay: 'Internal Documents'
  description: 'A list of documents for internal upload and use.'
-
  scope: 'Attendance'
  name: 'countClassAsSchool'
  nameDisplay: 'Count Class Attendance as School Attendance'
  description: 'Should attendance from the class context be used to prefill and inform school attendance?'
  value: 'N'
-
  scope: 'Attendance'
  name: 'defaultRollGroupAttendanceType'
  nameDisplay: 'Default Roll Group Attendance Type'
  description: 'The default selection for attendance type when taking Roll Group attendance'
  value: 'Present'
-
  scope: 'Attendance'
  name: 'defaultClassAttendanceType'
  nameDisplay: 'Default Class Attendance Type'
  description: 'The default selection for attendance type when taking Class attendance'
  value: 'Present'
-
  scope: 'Application Form'
  name: 'postscript'
  nameDisplay: 'Postscript'
  description: 'Information to display at the end of the form'
-
  scope: 'Students'
  name: 'academicAlertLowThreshold'
  nameDisplay: 'Low Academic Alert Threshold'
  description: 'The number of Mark book concerns needed in the past 60 days to raise a low level academic alert on a student.'
  value: 3
-
  scope: 'Students'
  name: 'academicAlertMediumThreshold'
  nameDisplay: 'Medium Academic Alert Threshold'
  description: 'The number of Mark book concerns needed in the past 60 days to raise a medium level academic alert on a student.'
  value: 6
-
  scope: 'Students'
  name: 'academicAlertHighThreshold'
  nameDisplay: 'High Academic Alert Threshold'
  description: 'The number of Mark book concerns needed in the past 60 days to raise a high level academic alert on a student.'
  value: 9
-
  scope: 'Students'
  name: 'behaviourAlertLowThreshold'
  nameDisplay: 'Low Behaviour Alert Threshold'
  description: 'The number of Behaviour concerns needed in the past 60 days to raise a low level alert on a student.'
  value: 3
-
  scope: 'Students'
  name: 'behaviourAlertMediumThreshold'
  nameDisplay: 'Medium Behaviour Alert Threshold'
  description: 'The number of Behaviour concerns needed in the past 60 days to raise a medium level alert on a student.'
  value: 6
-
  scope: 'Students'
  name: 'behaviourAlertHighThreshold'
  nameDisplay: 'High Behaviour Alert Threshold'
  description: 'The number of Behaviour concerns needed in the past 60 days to raise a high level alert on a student.'
  value: 9
-
  scope: 'Mark Book'
  name: 'enableDisplayCumulativeMarks'
  nameDisplay: 'Enable Display Cumulative Marks'
  description: 'Should cumulative marks be displayed on the View Mark book page for Students and Parents and in Student Profiles?'
  value: 'N'
-
  scope: 'Application Form'
  name: 'scholarshipOptionsActive'
  nameDisplay: 'Scholarship Options Active'
  description: 'Should the Scholarship Options section be turned on?'
  value: 'Y'
-
  scope: 'Application Form'
  name: 'paymentOptionsActive'
  nameDisplay: 'Payment Options Active'
  description: 'Should the Payment section be turned on?'
  value: 'Y'
-
  scope: 'Application Form'
  name: 'senOptionsActive'
  nameDisplay: 'Special Education Needs Active'
  description: 'Should the Special Education Needs section be turned on?'
  value: 'Y'
-
  scope: 'Application Form'
  name: 'scholarships'
  nameDisplay: 'Scholarships'
  description: 'Information to display before the scholarship options'
-
  scope: 'Timetable Admin'
  name: 'autoEnrolCourses'
  nameDisplay: 'Auto-Enrol Courses Default'
  description: 'Should auto-enrolment of new students into courses be turned on or off by default?'
  value: 'N'
-
  scope: 'Application Form'
  name: 'availableYearsOfEntry'
  nameDisplay: 'Available Years of Entry'
  description: 'Which school years should be available to apply to?'
-
  scope: 'Application Form'
  name: 'enableLimitedYearsOfEntry'
  nameDisplay: 'Enable Limited Years of Entry'
  description: 'If yes, applicants choices for Year of Entry can be limited to specific school years.'
  value: 'N'
-
  scope: 'People'
  name: 'uniqueEmailAddress'
  nameDisplay: 'Unique Email Address'
  description: 'Are primary email addresses required to be unique?'
  value: 'Y'
-
  scope: 'Planner'
  name: 'parentWeeklyEmailSummaryIncludeMark book'
  nameDisplay: 'Parent Weekly Email Summary Include Mark book'
  description: 'Should Mark book information be included in the weekly planner email summary that goes out to parents?'
  value: 'N'
-
  scope: 'System'
  name: 'nameFormatStaffFormal'
  nameDisplay: 'Formal Name Format'
  description: ''
  value: '[title] [preferredName:1]. [surname]'
-
  scope: 'System'
  name: 'nameFormatStaffFormalReversed'
  nameDisplay: 'Formal Name Reversed'
  description: ''
  value: '[title] [surname], [preferredName:1].'
-
  scope: 'System'
  name: 'nameFormatStaffInformal'
  nameDisplay: 'Informal Name Format'
  description: ''
  value: '[preferredName] [surname]'
-
  scope: 'System'
  name: 'nameFormatStaffInformalReversed'
  nameDisplay: 'Informal Name Reversed'
  description: ''
  value: '[surname], [preferredName]'
-
  scope: 'Attendance'
  name: 'selfRegistrationRedirect'
  nameDisplay: 'Self Registration Redirect'
  description: 'Should self registration redirect to Message Wall?'
  value: 'N'
-
  scope: 'Application Form'
  name: 'agreement'
  nameDisplay: 'Agreement'
  description: 'Without this text, which is displayed above the agreement, users will not be asked to agree to anything'
-
  scope: 'Data Updater'
  name: 'cutoffDate'
  nameDisplay: 'Cutoff Date'
  description: 'Earliest acceptable date when checking if data updates are required.'
-
  scope: 'Data Updater'
  name: 'redirectByRoleCategory'
  nameDisplay: 'Data Updater Redirect'
  description: 'Which types of users should be redirected to the Data Updater if updates are required.'
  value: 'Parent'
-
  scope: 'Data Updater'
  name: 'requiredUpdates'
  nameDisplay: 'Required Updates?'
  description: 'Should the data updater highlight updates that are required?'
  value: 'N'
-
  scope: 'Data Updater'
  name: 'requiredUpdatesByType'
  nameDisplay: 'Required Update Types'
  description: 'Which type of data updates should be required.'
  value: 'Personal,Family'
-
  scope: 'Mark Book'
  name: 'enableModifiedAssessment'
  nameDisplay: 'Enable Modified Assessment'
  description: 'Allows teachers to specify ''Modified Assessment'' for students with individual needs.'
  value: 'N'
-
  scope: 'Messenger'
  name: 'messageBcc'
  nameDisplay: 'Message Bcc'
  description: 'A list of recipients to bcc all messenger emails to.'
-
  scope: 'System'
  name: 'organisationBackground'
  nameDisplay: 'Background'
  description: 'Relative path to background image. Overrides theme background.'
-
  scope: 'Messenger'
  name: 'smsGateway'
  nameDisplay: 'SMS Gateway'
  description: ''
-
  scope: 'Messenger'
  name: 'smsSenderID'
  nameDisplay: 'SMS Sender ID'
  description: 'The sender name or phone number. Depends on the gateway used.'
-
  scope: 'System Admin'
  name: 'exportDefaultFileType'
  nameDisplay: 'Default Export File Type'
  description: ''
  value: 'Excel2007'
-
  scope: 'Application Form'
  name: 'publicApplications'
  nameDisplay: 'Public Applications?'
  description: 'If yes, members of the public can submit applications'
  value: 'N'
-
  scope: 'Staff'
  name: 'substituteTypes'
  nameDisplay: 'Substitute Types'
  description: 'A list of Substitution Types used at your school.'
  value: 'Internal Substitute,External Substitute'
-
  scope: 'Staff'
  name: 'urgencyThreshold'
  nameDisplay: 'Urgency Threshold'
  description: 'Notifications in this time-span are sent immediately, day or night.'
  value: 3
-
  scope: 'Staff'
  name: 'urgentNotifications'
  nameDisplay: 'Urgent Notifications'
  description: 'If enabled, urgent notifications will be sent by SMS as well as email.'
  value: 'N'
-
  scope: 'Staff'
  name: 'absenceApprovers'
  nameDisplay: 'Absence Approvers'
  description: 'Users who can approve staff absences. Leave this blank if approval is not used.'
-
  scope: 'Staff'
  name: 'absenceFullDayThreshold'
  nameDisplay: 'Full Day Absence'
  description: 'The minumum number of hours for an absence to count as a full day (1.0)'
  value: 6.0
-
  scope: 'Staff'
  name: 'absenceHalfDayThreshold'
  nameDisplay: 'Half Day Absence'
  description: 'The minumum number of hours for an absence to count as a half day (.5). Absences less than this count as 0'
  value: 2.0
-
  scope: 'Staff'
  name: 'absenceNotificationGroups'
  nameDisplay: 'Notification Groups'
  description: 'Which messenger groups can staff members send absence notifications to?'
-
  scope: 'Attendance'
  name: 'crossFillClasses'
  nameDisplay: 'Cross-Fill Classes'
  description: 'Should classes prefill with data from other classes?'
  value: 'N'
-
  scope: 'Behaviour'
  name: 'positiveDescriptors'
  nameDisplay: 'Positive Descriptors'
  description: 'Allowable choices for positive behaviour'
  value: 'Attitude to learning,Collaboration,Community spirit,Creativity,Effort,Leadership,Participation,Persistence,Problem solving,Quality of work,Values'
-
  scope: 'Behaviour'
  name: 'negativeDescriptors'
  nameDisplay: 'Negative Descriptors'
  description: 'Allowable choices for negative behaviour'
  value: 'Classwork - Late,Classwork - Incomplete,Classwork - Unacceptable,Disrespectful,Disruptive,Homework - Late,Homework - Incomplete,Homework - Unacceptable,ICT Misuse,Truancy,Other'
-
  scope: 'Behaviour'
  name: 'levels'
  nameDisplay: 'Levels'
  description: 'Allowable choices for severity level (from lowest to highest)'
  value: ',Stage 1,Stage 1 (Actioned),Stage 2,Stage 2 (Actioned),Stage 3,Stage 3 (Actioned),Actioned'
-
  scope: 'Resources'
  name: 'categories'
  nameDisplay: 'Categories'
  description: 'Allowable choices for category'
  value: 'Article,Book,Document,Graphic,Idea,Music,Object,Painting,Person,Photo,Place,Poetry,Prose,Rubric,Text,Video,Website,Work Sample,Other'
-
  scope: 'System'
  name: 'organisationAbbreviation'
  nameDisplay: 'Organisation Name Abbreviation'
  description: ''
  value: 'HRS'
-
  scope: 'Resources'
  name: 'purposesGeneral'
  nameDisplay: 'Purposes (General)'
  description: 'Allowable choices for purpose when creating a resource'
  value: 'Assessment Aid,Concept,Inspiration,Learner Profile,Mass Mailer Attachment,Provocation,Skill,Teaching and Learning Strategy,Other'
-
  scope: 'System'
  name: 'version'
  nameDisplay: 'Version'
  description: 'The version of the Gibbon database'
  value: '0.0.00'
-
  scope: 'Resources'
  name: 'purposesRestricted'
  nameDisplay: 'Purposes (Restricted)'
  description: 'Additional allowable choices for purpose when creating a resource, for those with ''Manage All Resources'' rights'
-
  scope: 'System'
  name: 'organisationEmail'
  nameDisplay: 'Organisation Email'
  description: 'General email address for the school'
-
  scope: 'Activities'
  name: 'dateType'
  nameDisplay: 'Date Type'
  description: 'Should activities be organised around dates (flexible) or terms (easy)?'
  value: 'Term'
-
  scope: 'System'
  name: 'installType'
  nameDisplay: 'Install Type'
  description: 'The purpose of this installation of Kookaburra'
  value: 'Development'
-
  scope: 'System'
  name: 'statsCollection'
  nameDisplay: 'Statistics Collection'
  description: 'To track Gibbon uptake, the system tracks basic data (current URL, install type, organisation name) on each install. Do you want to help?'
  value: 'Y'
-
  scope: 'Activities'
  name: 'maxPerTerm'
  nameDisplay: 'Maximum Activities per Term'
  description: 'The most a student can sign up for in one term. Set to 0 for unlimited.'
  value: 0
-
  scope: 'Planner'
  name: 'lessonDetailsTemplate'
  nameDisplay: 'Lesson Details Template'
  description: 'Template to be inserted into Lesson Details field'
-
  scope: 'Planner'
  name: 'teachersNotesTemplate'
  nameDisplay: 'Teacher''s Notes Template'
  description: 'Template to be inserted into Teacher''s Notes field'
-
  scope: 'System'
  name: 'pagination'
  nameDisplay: 'Pagination Count'
  description: 'Must be numeric. Number of records shown per page.'
  value: 25
-
  scope: 'Planner'
  name: 'smartBlockTemplate'
  nameDisplay: 'Smart Block Template'
  description: 'Template to be inserted into new block in Smart Unit'
-
  scope: 'Planner'
  name: 'unitOutlineTemplate'
  nameDisplay: 'Unit Outline Template'
  description: 'Template to be inserted into Unit Outline section of planner'
-
  scope: 'Application Form'
  name: 'milestones'
  nameDisplay: 'Milestones'
  description: 'A list of the major steps in the application process. Applicants can be tracked through the various stages.'
-
  scope: 'Library'
  name: 'defaultLoanLength'
  nameDisplay: 'Default Loan Length'
  description: 'The standard loan length for a library item, in days'
  value: 7
-
  scope: 'Behaviour'
  name: 'policyLink'
  nameDisplay: 'Policy Link'
  description: 'A link to the school behaviour policy.'
-
  scope: 'Library'
  name: 'browseBGColor'
  nameDisplay: 'Browse Library BG Color'
  description: 'RGB Hex value, without leading #. Background color used behind library browsing screen.'
-
  scope: 'Library'
  name: 'browseBGImage'
  nameDisplay: 'Browse Library BG Image'
  description: 'URL to background image used behind library browsing screen.'
-
  scope: 'System'
  name: 'passwordPolicyAlpha'
  nameDisplay: 'Password - Alpha Requirement'
  description: 'Require both upper and lower case alpha characters?'
  value: 'Y'
-
  scope: 'System'
  name: 'passwordPolicyNumeric'
  nameDisplay: 'Password - Numeric Requirement'
  description: 'Require at least one numeric character?'
  value: 'Y'
-
  scope: 'System'
  name: 'passwordPolicyNonAlphaNumeric'
  nameDisplay: 'Password - Non-Alphanumeric Requirement'
  description: 'Require at least one non-alphanumeric character (e.g. punctuation mark or space)?'
  value: 'N'
-
  scope: 'System'
  name: 'systemName'
  nameDisplay: 'System Name'
  description: ''
  value: 'Quoll'
-
  scope: 'System'
  name: 'passwordPolicyMinLength'
  nameDisplay: 'Password - Minimum Length'
  description: 'Minimum acceptable password length.'
  value: 8
-
  scope: 'People'
  name: 'ethnicity'
  nameDisplay: 'Ethnicity'
  description: 'A list of ethnicities available in system'
-
  scope: 'People'
  name: 'nationality'
  nameDisplay: 'Nationality'
  description: 'A list of nationalities available in system. If blank, system will default to list of countries'
-
  scope: 'People'
  name: 'residencyStatus'
  nameDisplay: 'Residency Status'
  description: 'A list of residency status available in system. If blank, system will allow text input'
-
  scope: 'People'
  name: 'personalDataUpdaterRequiredFields'
  nameDisplay: 'Personal Data Updater Required Fields'
  description: 'Serialized array listed personal fields in data updater, and whether or not they are required.'
  value: 'a:4:{s:5:''Staff'';a:34:{s:5:''title'';s:8:''required'';s:7:''surname'';s:8:''required'';s:9:''firstName'';s:0:'''';s:13:''preferredName'';s:8:''required'';s:12:''officialName'';s:8:''required'';s:16:''nameInCharacters'';s:0:'''';s:3:''dob'';s:0:'''';s:5:''email'';s:0:'''';s:14:''emailAlternate'';s:0:'''';s:8:''address1'';s:8:''required'';s:8:''address2'';s:0:'''';s:6:''phone1'';s:0:'''';s:6:''phone2'';s:0:'''';s:6:''phone3'';s:0:'''';s:6:''phone4'';s:0:'''';s:13:''languageFirst'';s:0:'''';s:14:''languageSecond'';s:0:'''';s:13:''languageThird'';s:0:'''';s:14:''countryOfBirth'';s:0:'''';s:9:''ethnicity'';s:0:'''';s:8:''religion'';s:0:'''';s:12:''citizenship1'';s:0:'''';s:20:''citizenship1Passport'';s:0:'''';s:12:''citizenship2'';s:0:'''';s:20:''citizenship2Passport'';s:0:'''';s:20:''nationalIDCardNumber'';s:0:'''';s:15:''residencyStatus'';s:0:'''';s:14:''visaExpiryDate'';s:0:'''';s:10:''profession'';s:0:'''';s:8:''employer'';s:0:'''';s:8:''jobTitle'';s:0:'''';s:19:''vehicleRegistration'';s:0:'''';s:17:''emergencyContact1'';s:8:''required'';s:17:''emergencyContact2'';s:0:'''';}s:7:''Student'';a:34:{s:5:''title'';s:8:''required'';s:7:''surname'';s:8:''required'';s:9:''firstName'';s:8:''required'';s:13:''preferredName'';s:8:''required'';s:12:''officialName'';s:8:''required'';s:16:''nameInCharacters'';s:0:'''';s:3:''dob'';s:0:'''';s:5:''email'';s:0:'''';s:14:''emailAlternate'';s:0:'''';s:8:''address1'';s:8:''required'';s:8:''address2'';s:0:'''';s:6:''phone1'';s:0:'''';s:6:''phone2'';s:0:'''';s:6:''phone3'';s:0:'''';s:6:''phone4'';s:0:'''';s:13:''languageFirst'';s:0:'''';s:14:''languageSecond'';s:0:'''';s:13:''languageThird'';s:0:'''';s:14:''countryOfBirth'';s:0:'''';s:9:''ethnicity'';s:0:'''';s:8:''religion'';s:0:'''';s:12:''citizenship1'';s:0:'''';s:20:''citizenship1Passport'';s:0:'''';s:12:''citizenship2'';s:0:'''';s:20:''citizenship2Passport'';s:0:'''';s:20:''nationalIDCardNumber'';s:0:'''';s:15:''residencyStatus'';s:0:'''';s:14:''visaExpiryDate'';s:0:'''';s:10:''profession'';s:0:'''';s:8:''employer'';s:0:'''';s:8:''jobTitle'';s:0:'''';s:19:''vehicleRegistration'';s:0:'''';s:17:''emergencyContact1'';s:0:'''';s:17:''emergencyContact2'';s:0:'''';}s:6:''Parent'';a:34:{s:5:''title'';s:8:''required'';s:7:''surname'';s:8:''required'';s:9:''firstName'';s:0:'''';s:13:''preferredName'';s:8:''required'';s:12:''officialName'';s:8:''required'';s:16:''nameInCharacters'';s:0:'''';s:3:''dob'';s:0:'''';s:5:''email'';s:0:'''';s:14:''emailAlternate'';s:0:'''';s:8:''address1'';s:8:''required'';s:8:''address2'';s:0:'''';s:6:''phone1'';s:0:'''';s:6:''phone2'';s:0:'''';s:6:''phone3'';s:0:'''';s:6:''phone4'';s:0:'''';s:13:''languageFirst'';s:0:'''';s:14:''languageSecond'';s:0:'''';s:13:''languageThird'';s:0:'''';s:14:''countryOfBirth'';s:0:'''';s:9:''ethnicity'';s:0:'''';s:8:''religion'';s:0:'''';s:12:''citizenship1'';s:0:'''';s:20:''citizenship1Passport'';s:0:'''';s:12:''citizenship2'';s:0:'''';s:20:''citizenship2Passport'';s:0:'''';s:20:''nationalIDCardNumber'';s:0:'''';s:15:''residencyStatus'';s:0:'''';s:14:''visaExpiryDate'';s:0:'''';s:10:''profession'';s:0:'''';s:8:''employer'';s:0:'''';s:8:''jobTitle'';s:0:'''';s:19:''vehicleRegistration'';s:0:'''';s:17:''emergencyContact1'';s:0:'''';s:17:''emergencyContact2'';s:0:'''';}s:5:''Other'';a:34:{s:5:''title'';s:8:''required'';s:7:''surname'';s:8:''required'';s:9:''firstName'';s:0:'''';s:13:''preferredName'';s:8:''required'';s:12:''officialName'';s:8:''required'';s:16:''nameInCharacters'';s:0:'''';s:3:''dob'';s:0:'''';s:5:''email'';s:0:'''';s:14:''emailAlternate'';s:0:'''';s:8:''address1'';s:0:'''';s:8:''address2'';s:0:'''';s:6:''phone1'';s:0:'''';s:6:''phone2'';s:0:'''';s:6:''phone3'';s:0:'''';s:6:''phone4'';s:0:'''';s:13:''languageFirst'';s:0:'''';s:14:''languageSecond'';s:0:'''';s:13:''languageThird'';s:0:'''';s:14:''countryOfBirth'';s:0:'''';s:9:''ethnicity'';s:0:'''';s:8:''religion'';s:0:'''';s:12:''citizenship1'';s:0:'''';s:20:''citizenship1Passport'';s:0:'''';s:12:''citizenship2'';s:0:'''';s:20:''citizenship2Passport'';s:0:'''';s:20:''nationalIDCardNumber'';s:0:'''';s:15:''residencyStatus'';s:0:'''';s:14:''visaExpiryDate'';s:0:'''';s:10:''profession'';s:0:'''';s:8:''employer'';s:0:'''';s:8:''jobTitle'';s:0:'''';s:19:''vehicleRegistration'';s:0:'''';s:17:''emergencyContact1'';s:0:'''';s:17:''emergencyContact2'';s:0:'''';}}'
-
  scope: 'School Admin'
  name: 'primaryExternalAssessmentByYearGroup'
  nameDisplay: 'Primary External Assessment By Year Group'
  description: 'Serialized array connected gibbonExternalAssessmentID to gibbonYearGroupID, and specify which field set to use.'
  value: 'a:7:{i:1;s:21:''1-2_KS3 Target Grades'';i:2;s:22:''1-3_GCSE Target Grades'';i:3;s:10:''1-1_Scores'';i:4;s:0:'''';i:5;s:0:'''';i:6;s:0:'''';i:7;s:0:'''';}'
-
  scope: 'Mark Book'
  name: 'markBookType'
  nameDisplay: 'Mark Book Type'
  description: 'A list of types to make available in the Mark book.'
  value: 'Essay,Exam,Homework,Reflection,Test,Unit,End of Year,Other'
-
  scope: 'System'
  name: 'allowableHTML'
  nameDisplay: 'Allowable HTML'
  description: 'TinyMCE-style list of acceptable HTML tags and options.'
  value: 'br[style],strong[style],em[style],span[style],p[style],address[style],pre[style],h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],table[style],thead[style],tbody[style],tfoot[style],tr[style],td[style|colspan|rowspan],ol[style],ul[style],li[style],blockquote[style],a[style|target|href],img[style|class|src|width|height],video[style],source[style],hr[style],iframe[style|width|height|src|frameborder|allowfullscreen],embed[style],div[style],sup[style],sub[style]'
-
  scope: 'Application Form'
  name: 'howDidYouHear'
  nameDisplay: 'How Did Your Hear?'
  description: 'A list'
  value: 'Advertisement,Personal Recommendation,World Wide Web,Others'
-
  scope: 'Messenger'
  name: 'smsUsername'
  nameDisplay: 'SMS Username'
  description: 'SMS gateway username.'
-
  scope: 'System'
  name: 'indexText'
  nameDisplay: 'Index Page Text'
  description: 'Text displayed in system''s welcome page.'
  value: 'Welcome to Kookaburra, the free, open, flexible school platform. Designed by teachers for learning, Kookaburra gives you the school tools you need. Kookaburra is a fork of Gibbon.'
-
  scope: 'Messenger'
  name: 'smsPassword'
  nameDisplay: 'SMS Password'
  description: 'SMS gateway password.'
-
  scope: 'Messenger'
  name: 'smsURL'
  nameDisplay: 'SMS URL'
  description: 'SMS gateway URL for send requests.'
-
  scope: 'Messenger'
  name: 'smsURLCredit'
  nameDisplay: 'SMS URL Credit'
  description: 'SMS gateway URL for checking credit.'
-
  scope: 'System'
  name: 'currency'
  nameDisplay: 'Currency'
  description: 'System-wide currency for financial transactions. Support for online payment in this currency depends on your credit card gateway: please consult their support documentation.'
-
  scope: 'System'
  name: 'enablePayments'
  nameDisplay: 'Enable Payments'
  description: 'Should payments be enabled across the system?'
  value: 'N'
-
  scope: 'System'
  name: 'paypalAPIUsername'
  nameDisplay: 'PayPal API Username'
  description: 'API Username provided by PayPal.'
-
  scope: 'System'
  name: 'paypalAPIPassword'
  nameDisplay: 'PayPal API Password'
  description: 'API Password provided by PayPal.'
-
  scope: 'System'
  name: 'paypalAPISignature'
  nameDisplay: 'PayPal API Signature'
  description: 'API Signature provided by PayPal.'
-
  scope: 'Application Form'
  name: 'applicationFee'
  nameDisplay: 'Application Fee'
  description: 'The cost of applying to the school.'
  value: 0
-
  scope: 'Application Form'
  name: 'requiredDocuments'
  nameDisplay: 'Required Documents'
  description: 'A list of documents which must be submitted electronically with the application form.'
-
  scope: 'Application Form'
  name: 'requiredDocumentsCompulsory'
  nameDisplay: 'Required Documents Compulsory?'
  description: 'Are the required documents compulsory?'
  value: 'N'
-
  scope: 'Application Form'
  name: 'requiredDocumentsText'
  nameDisplay: 'Required Documents Text'
  description: 'Explanatory text to appear with the required documents?'
-
  scope: 'Application Form'
  name: 'notificationStudentDefault'
  nameDisplay: 'Student Notification Default'
  description: 'Should student acceptance email be turned on or off by default.'
  value: 'On'
-
  scope: 'Application Form'
  name: 'languageOptionsActive'
  nameDisplay: 'Language Options Active'
  description: 'Should the Language Options section be turned on?'
  value: 'Off'
-
  scope: 'Application Form'
  name: 'languageOptionsBlurb'
  nameDisplay: 'Language Options Blurb'
  description: 'Introductory text if Language Options section is turned on.'
-
  scope: 'Application Form'
  name: 'languageOptionsLanguageList'
  nameDisplay: 'Language Options Language List'
  description: 'A list of available language selections if Language Options section is turned on.'
-
  scope: 'People'
  name: 'personalBackground'
  nameDisplay: 'Personal Background'
  description: 'Should users be allowed to set their own personal backgrounds?'
  value: 'Y'
-
  scope: 'People'
  name: 'dayTypeOptions'
  nameDisplay: 'Day-Type Options'
  description: 'A list of options to make available (e.g. half-day, full-day). If blank, this field will not show up in the application form.'
-
  scope: 'People'
  name: 'dayTypeText'
  nameDisplay: 'Day-Type Text'
  description: 'Explanatory text to include with Day-Type Options.'
-
  scope: 'Mark Book'
  name: 'showStudentAttainmentWarning'
  nameDisplay: 'Show Student Attainment Warning'
  description: 'Show low attainment grade visual warning to students?'
  value: 'Y'
-
  scope: 'Mark Book'
  name: 'showStudentEffortWarning'
  nameDisplay: 'Show Student Effort Warning'
  description: 'Show low effort grade visual warning to students?'
  value: 'Y'
-
  scope: 'Mark Book'
  name: 'showParentAttainmentWarning'
  nameDisplay: 'Show Parent Attainment Warning'
  description: 'Show low attainment grade visual warning to parents?'
  value: 'Y'
-
  scope: 'Mark Book'
  name: 'showParentEffortWarning'
  nameDisplay: 'Show Parent Effort Warning'
  description: 'Show low effort grade visual warning to parents?'
  value: 'Y'
-
  scope: 'Planner'
  name: 'allowOutcomeEditing'
  nameDisplay: 'Allow Outcome Editing'
  description: 'Should the text within outcomes be editable when planning lessons and units?'
  value: 'Y'
-
  scope: 'People'
  name: 'privacy'
  nameDisplay: 'Privacy'
  description: 'Should privacy options be turned on across the system?'
  value: 'N'
-
  scope: 'People'
  name: 'privacyBlurb'
  nameDisplay: 'Privacy Blurb'
  description: 'Descriptive text to accompany image privacy option when shown to users.'
-
  scope: 'Finance'
  name: 'invoiceText'
  nameDisplay: 'Invoice Text'
  description: 'Text to appear in invoice, above invoice details and fees.'
-
  scope: 'Finance'
  name: 'invoiceNotes'
  nameDisplay: 'Invoice Notes'
  description: 'Text to appear in invoice, below invoice details and fees.'
-
  scope: 'Finance'
  name: 'receiptText'
  nameDisplay: 'Receipt Text'
  description: 'Text to appear in receipt, above receipt details and fees.'
-
  scope: 'Finance'
  name: 'receiptNotes'
  nameDisplay: 'Receipt Notes'
  description: 'Text to appear in receipt, below receipt details and fees.'
-
  scope: 'System'
  name: 'analytics'
  nameDisplay: 'Analytics'
  description: 'Javascript code to integrate statistics, such as Google Analytics'
-
  scope: 'Finance'
  name: 'reminder1Text'
  nameDisplay: 'Reminder 1 Text'
  description: 'Text to appear in first level reminder level, above invoice details and fees.'
-
  scope: 'Finance'
  name: 'reminder2Text'
  nameDisplay: 'Reminder 2 Text'
  description: 'Text to appear in second level reminder level, above invoice details and fees.'
-
  scope: 'Finance'
  name: 'reminder3Text'
  nameDisplay: 'Reminder 3 Text'
  description: 'Text to appear in third level reminder level, above invoice details and fees.'
-
  scope: 'Finance'
  name: 'email'
  nameDisplay: 'Email'
  description: 'Email address to send finance emails from.'
  value: 'craig@craigrayner.com'
-
  scope: 'Application Form'
  name: 'notificationParentsDefault'
  nameDisplay: 'Parents Notification Default'
  description: 'Should parent acceptance email be turned on or off by default.'
  value: 'On'
-
  scope: 'People'
  name: 'privacyOptions'
  nameDisplay: 'Privacy Options'
  description: 'A list of choices to make available if privacy options are turned on. If blank, privacy fields will not be displayed.'
-
  scope: 'Planner'
  name: 'sharingDefaultParents'
  nameDisplay: 'Sharing Default: Parents'
  description: 'When adding lessons and deploying units, should sharing default for parents be Y or N?'
  value: 'Y'
-
  scope: 'Planner'
  name: 'sharingDefaultStudents'
  nameDisplay: 'Sharing Default: Students'
  description: 'When adding lessons and deploying units, should sharing default for students be Y or N?'
  value: 'Y'
-
  scope: 'Students'
  name: 'extendedBriefProfile'
  nameDisplay: 'Extended Brief Profile'
  description: 'The extended version of the brief student profile includes contact information of parents.'
  value: 'N'
-
  scope: 'Application Form'
  name: 'notificationParentsMessage'
  nameDisplay: 'Parents Notification Message'
  description: 'A custom message to add to the standard email to parents on acceptance.'
");
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
