<?php
 
class EmailPermutator {
 
	private $fname; 
	private $lname;
	private $dname;
	private $fn_letter;
	private $ln_letter;

	public static $spcialChars = array('-', '--', '.', '..', '_', '__');
	public $email_permutators = array();
	 
	public function __construct($fname, $lname,$dname) {
		$this->fname =  $fname;
		$this->lname = $lname;
		$this->dname = $dname;

		$this->fn_letter = substr($this->fname, 0, 1);
		$this->ln_letter = substr($this->lname, 0, 1);
	}
	 
	public function get_fname() {
		return $this->fname;
	}
	 
	public function set_fname($fname) {
		$this->fname = $fname;
	}
	 
	public function get_lname() {
		return $this->lname;
	}
	 
	public function set_lname($name) {
		$this->lname = $lname;
	}

	public function get_dname() { 
		return $this->dname;
	}
	 
	public function set_dname($name) { 
		$this->dname = $dname;
	}

	public function addSpecialCharBetweenNames(){
	    foreach(self::$spcialChars as $spcialChar){
			$this->email_permutators[]= $this->fname . $spcialChar . $this->lname . '@' . $this->dname;
			$this->email_permutators[]= $this->lname . $spcialChar . $this->fname . '@' . $this->dname;
			$this->email_permutators[]= $this->fn_letter . $spcialChar.$this->lname.'@' . $this->dname;
			$this->email_permutators[]= $this->fname . $spcialChar . $this->ln_letter . '@' . $this->dname;
			$this->email_permutators[]= $this->fn_letter . $spcialChar . $this->ln_letter . '@' . $this->dname;
		}

		$this->email_permutators[]= $this->fname . $this->lname .'@' . $this->dname;
		$this->email_permutators[]= $this->lname . $this->fname .'@' . $this->dname;
		$this->email_permutators[]= $this->lname . $this->fn_letter . '@' . $this->dname;
		$this->email_permutators[]= $this->ln_letter . '.' . $this->fn_letter . '@' . $this->dname;
		$this->email_permutators[]= $this->ln_letter . '.' . $this->fname .'@' . $this->dname;
		$this->email_permutators[]= $this->ln_letter . $this->fn_letter . '@' . $this->dname;

		// Cases
		// ousbey.r@distilled.net
		// orob@distilled.net
		// ousbey-r@distilled.net
		// o-rob@distilled.net
		// o-r@distilled.net
		// ousbey_r@distilled.net
		// o_rob@distilled.net
		// o_r@distilled.net
	}

	public function addEmailOnlyLnameORfname(){
		$this->email_permutators[]= $this->fname .'@' . $this->dname;
		$this->email_permutators[]= $this->lname .'@' . $this->dname;
		$this->email_permutators[]= $this->lname . $this->fn_letter .'@' . $this->dname;
	}

	public function addEmailByFirstCharCombination(){
		$this->email_permutators[]= $this->fn_letter . $this->lname . '@'  . $this->dname;
		$this->email_permutators[]= $this->fname . $this->ln_letter . '@'  . $this->dname;
		$this->email_permutators[]= $this->fn_letter . $this->ln_letter . '@' . $this->dname;	
	}

}