<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Audit\Model;

use Pop\Audit\Auditor;

/**
 * Abstract auditable model class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.3
 */
abstract class AuditableModel extends \Pop\Model\AbstractModel implements AuditableInterface
{

    /**
     * Auditor object
     * @var Auditor
     */
    protected $auditor = null;

    /**
     * Set the auditor object
     *
     * @param  Auditor $auditor
     * @return self
     */
    public function setAuditor(Auditor $auditor)
    {
        $this->auditor = $auditor;
        return $this;
    }

    /**
     * Get the auditor object
     *
     * @return Auditor
     */
    public function getAuditor()
    {
        return $this->auditor;
    }

    /**
     * Determine if the model has auditor
     *
     * @return boolean
     */
    public function hasAuditor()
    {
        return (null !== $this->auditor);
    }

    /**
     * Determine if the model is auditable
     *
     * @return boolean
     */
    public function isAuditable()
    {
        return (null !== $this->auditor);
    }

}