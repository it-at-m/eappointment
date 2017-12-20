<?php
/**
 *
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use \BO\Mellon\Validator;

class OrganisationData
{
    protected $category = '';

    protected $scope = null;

    protected $department = null;

    protected $organisation = null;

    protected $type = 'xlsx';

    public function __construct($type, $scope = null, $department = null, $organisation = null)
    {
        $this->type = $type;
        $this->scope = $scope;
        $this->department = $department;
        $this->organisation = $organisation;
    }

    public function getScopeName()
    {
        return $this->scope->contact['name'] .' '. $this->scope->shortname;
    }

    public function getDepartmentName()
    {
        return $this->department->name;
    }

    public function getOrganisationName()
    {
        return $this->organisation->name;
    }

    public function setData($subject, $subjectId)
    {
        if (false !== strpos($subject, 'scope')) {
            $this->scope = \App::$http->readGetResult('/scope/'. $subjectId .'/')->getEntity();
            $this->department = \App::$http->readGetResult('/scope/'. $this->scope->id .'/department/')->getEntity();
            $this->organisation = \App::$http
                ->readGetResult('/department/'. $this->department->id .'/organisation/')->getEntity();
        }
        if (false !== strpos($subject, 'department')) {
            $this->department = \App::$http->readGetResult('/department/'. $subjectId .'/')->getEntity();
            $this->organisation = \App::$http
                ->readGetResult('/department/'. $this->department->id .'/organisation/')->getEntity();
        }
        if (false !== strpos($subject, 'organisation')) {
            $this->organisation = \App::$http->readGetResult('/organisation/'. $subjectId .'/')->getEntity();
        }
        return $this;
    }

    //for csv export convert to windows charset
    private function convertToWindowsCharset($string)
    {
        $charset =  mb_detect_encoding(
            $string,
            "UTF-8, ISO-8859-1, ISO-8859-15",
            true
        );

        $string =  mb_convert_encoding($string, "Windows-1252", $charset);
        return $string;
    }
}
