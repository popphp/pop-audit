<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Audit\Model;

use Pop\Audit\Auditor;
use Pop\Model\AbstractDataModel;

/**
 * Abstract auditable model class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
abstract class AuditableModel extends AbstractDataModel implements AuditableInterface
{

    /**
     * Auditor object
     * @var ?Auditor
     */
    protected ?Auditor $auditor = null;

    /**
     * Set the auditor object
     *
     * @param  Auditor $auditor
     * @return AuditableModel
     */
    public function setAuditor(Auditor $auditor): AuditableModel
    {
        $this->auditor = $auditor;
        return $this;
    }

    /**
     * Get the auditor object
     *
     * @return Auditor|null
     */
    public function getAuditor(): Auditor|null
    {
        return $this->auditor;
    }

    /**
     * Determine if the model has auditor
     *
     * @return bool
     */
    public function hasAuditor(): bool
    {
        return ($this->auditor !== null);
    }

    /**
     * Determine if the model is auditable (alias)
     *
     * @return bool
     */
    public function isAuditable(): bool
    {
        return ($this->auditor !== null);
    }

}
