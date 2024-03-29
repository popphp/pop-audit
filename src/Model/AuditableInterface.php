<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
interface AuditableInterface
{

    /**
     * Set the auditor object
     *
     * @param  Auditor $auditor
     * @return AuditableInterface
     */
    public function setAuditor(Auditor $auditor): AuditableInterface;

    /**
     * Get the auditor object
     *
     * @return Auditor|null
     */
    public function getAuditor(): Auditor|null;

    /**
     * Determine if the model has auditor
     *
     * @return bool
     */
    public function hasAuditor(): bool;

    /**
     * Determine if the model is auditable
     *
     * @return bool
     */
    public function isAuditable(): bool;

}