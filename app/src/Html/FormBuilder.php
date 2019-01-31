<?php
namespace IsaiahExplained\Html;

use Illuminate\Html\FormBuilder as IlluminateFormBuilder;

class FormBuilder extends IlluminateFormBuilder {
    /**
     * An array containing the currently opened form groups.
     *
     * @var array
     */
    protected $groupStack = array();

    /**
     * Create a form input field.
     *
     * @param  string  $type
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function input($type, $name, $value = null, $options = array())
    {
        $options = $this->appendClassToOptions('form-control', $options);

        // Call the parent input method so that Laravel can handle
        // the rest of the input set up.
        return parent::input($type, $name, $value, $options);
    }

    /**
     * Create a select box field.
     *
     * @param  string  $name
     * @param  array   $list
     * @param  string  $selected
     * @param  array   $options
     * @return string
     */
    public function select($name, $list = array(), $selected = null, $options = array())
    {
        $options = $this->appendClassToOptions('form-control', $options);

        // Call the parent select method so that Laravel can handle
        // the rest of the select set up.
        return parent::select($name, $list, $selected, $options);
    }

    /**
     * Append the given class to the given options array.
     *
     * @param  string  $class
     * @param  array   $options
     * @return array
     */
    private function appendClassToOptions($class, array $options = array())
    {
        // If a 'class' is already specified, append the 'form-control'
        // class to it. Otherwise, set the 'class' to 'form-control'.
        $options['class'] = isset($options['class']) ? $options['class'].' ' : '';
        $options['class'] .= $class;

        return $options;
    }

    /**
     * Create a plain form input field.
     *
     * @param  string  $type
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function plainInput($type, $name, $value = null, $options = array())
    {
        return parent::input($type, $name, $value, $options);
    }

    /**
     * Create a plain select box field.
     *
     * @param  string  $name
     * @param  array   $list
     * @param  string  $selected
     * @param  array   $options
     * @return string
     */
    public function plainSelect($name, $list = array(), $selected = null, $options = array())
    {
        return parent::select($name, $list, $selected, $options);
    }

    /**
     * Determine whether the form element with the given name
     * has any validation errors.
     *
     * @param  string  $name
     * @return bool
     */
    private function hasErrors($name)
    {
        if (is_null($this->session) || ! $this->session->has('errors'))
        {
            // If the session is not set, or the session doesn't contain
            // any errors, the form element does not have any errors
            // applied to it.
            return false;
        }

        // Get the errors from the session.
        $errors = $this->session->get('errors');

        // Check if the errors contain the form element with the given name.
        // This leverages Laravel's transformKey method to handle the
        // formatting of the form element's name.
        return $errors->has($this->transformKey($name));
    }

    /**
     * Get the formatted errors for the form element with the given name.
     *
     * @param  string  $name
     * @return string
     */
    private function getFormattedErrors($name)
    {
        if ( ! $this->hasErrors($name))
        {
            // If the form element does not have any errors, return
            // an emptry string.
            return '';
        }

        // Get the errors from the session.
        $errors = $this->session->get('errors');

        // Return the formatted error message, if the form element has any.
        return $errors->first($this->transformKey($name), '<p class="help-block">:message</p>');
    }

    /**
     * Open a new form group.
     *
     * @param  string  $name
     * @param  mixed   $label
     * @param  array   $options
     * @return string
     */
    public function openGroup($name, $label = null, $options = array())
    {
        $options = $this->appendClassToOptions('form-group', $options);

        // Append the name of the group to the groupStack.
        $this->groupStack[] = $name;

        if ($this->hasErrors($name))
        {
            // If the form element with the given name has any errors,
            // apply the 'has-error' class to the group.
            $options = $this->appendClassToOptions('has-error', $options);
        }

        // If a label is given, we set it up here. Otherwise, we will just
        // set it to an empty string.
        $label = $label ? $this->label($name, $label) : '';

        return '<div'.$this->html->attributes($options).'>'.$label;
    }

    /**
     * Close out the last opened form group.
     *
     * @return string
     */
    public function closeGroup()
    {
        // Get the last added name from the groupStack and
        // remove it from the array.
        $name = array_pop($this->groupStack);

        // Get the formatted errors for this form group.
        $errors = $this->getFormattedErrors($name);

        // Append the errors to the group and close it out.
        return $errors.'</div>';
    }
}