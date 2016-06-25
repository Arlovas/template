<?php
/**
 * System_group_program Active Record
 * @author  <your-name-here>
 */
class SystemGroupProgram extends TRecord
{
    const TABLENAME = 'system_group_program';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('system_group_id');
        parent::addAttribute('system_program_id');
    }

    /**
     * Get all groups with permission in a determined program
     * @param  int   $programId foreign key of the program
     * @return array $groups    
     */
    public static function getGroupByProgram($programId)
    {
        $repository   = new TRepository('SystemGroupProgram');
        $gropPrograms = $repository->where('system_program_id', '=', $programId)->load();

        if(!$gropPrograms)
            return array();
        
        $groups = array();
        
        foreach ($gropPrograms as $groupProgram)
            $groups[] = $groupProgram->system_group_id;

        return $groups;
    }
}
?>