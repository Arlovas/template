<?php
/**
 * SystemProgramForm Registration
 * @author  <your name here>
 */
class SystemProgramForm extends TStandardForm
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
                
        // creates the form
        
        $this->form = new TQuickForm('form_SystemProgram');
        $this->form->setFormTitle(_t('Program'));
        $this->form->class = 'tform'; // CSS class
        
        // defines the database
        parent::setDatabase('permission');
        
        // defines the active record
        parent::setActiveRecord('SystemProgram');
        
        // create the form fields
        $id            = new TEntry('id');
        $name          = new TEntry('name');
        $controller    = new TEntry('controller');
        $groups        = new TCheckGroup('groups');

        $id->setEditable(false);
        
        TTransaction::open('permission');
        $groups->setLayout('horizontal');
        $groups->setBreakItems(2);
        $groups->addItems( SystemGroup::getGroups() );
        TTransaction::close();

        // add the fields
        $this->form->addQuickField('ID', $id,  50);
        $this->form->addQuickField(_t('Name') . ': ', $name,  200);
        $this->form->addQuickField(_t('Controller') . ': ', $controller,  200);
        $this->form->addQuickField(_t('Groups') . ': ', $groups,  200);

        // validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $controller->addValidation(('Controller'), new TRequiredValidator);

        // add form actions
        $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $this->form->addQuickAction(_t('New'), new TAction(array($this, 'onEdit')), 'fa:plus-square green');
        $this->form->addQuickAction(_t('Back to the listing'),new TAction(array('SystemProgramList','onReload')),'fa:table blue');

        $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml','SystemProgramList'));
        $container->addRow()->addCell($this->form);
        
        
        // add the form to the page
        parent::add($container);
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     * @param  $param An array containing the GET ($_GET) parameters
     */
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database
                TTransaction::open($this->database);
                
                $class = $this->activeRecord;
                
                // instantiates object
                $object = new $class($key);            

                //Get the programs of the group
                $object->groups = SystemGroupProgram::getGroupByProgram($object->id);

                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
                
                return $object;
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            // open a transaction with database
            TTransaction::open($this->database);
            
            // get the form data
            $object = $this->form->getData($this->activeRecord);
            
            // validate data
            $this->form->validate();
            
            // stores the object
            $object->store();

            //Save the gropus
            if($object->groups)
            {
                $this->saveGroups($object);
            }
            
            // fill the form with the active record data
            $this->form->setData($object);

            // close the transaction
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            
            return $object;
        }
        catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData($this->activeRecord);
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Prepare and save seletected groups of this program
     * @author Artur Comunello
     */
    public function saveGroups($object)
    {
        $groups = array();
        foreach ($object->groups as $key => $value)
        {
            $group     = new stdClass;
            $group->id = $value;
            $groups[]  = $group;
        }

        $object->saveAggregate('SystemGroupProgram', 'system_program_id', 'system_group_id', $object->id, $groups);
    }
}
?>