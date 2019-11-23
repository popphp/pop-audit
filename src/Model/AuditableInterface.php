<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Audit\Model;

use Pop\Audit\Auditor;

/**
 * Auditable model interface
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
interface AuditableInterface
{

    /**
     * Set the auditor object
     *
     * @param  Auditor $auditor
     * @return self
     */
    public function setAuditor(Auditor $auditor);

    /**
     * Get the auditor object
     *
     * @return Auditor
     */
    public function getAuditor();

    /**
     * Determine if the model has auditor
     *
     * @return Auditor
     */
    public function hasAuditor();

    /**
     * Determine if the model is auditable
     *
     * @return boolean
     */
    public function isAuditable();

}