<?php

# ini handler class
# coded by Alessandro Rosa
# e-mail : zandor_zz@yahoo.it
# site : http://malilla.supereva.it

# Copyright (C) 2006  Alessandro Rosa

# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

# Compiled with PHP 4.4.0

# July 27th 2006 : fixed a management bug for entries including (double) quotes by Joao Borges
# August 5th 2006 : fixed a "=" term management bug by replacing all calls 
#                   to custom version of 'parse_ini_file' function member
# November 19th 2006: fixed a bug with the 'p' character handling

class ini_manager
{
      function ini_manager()
      {
          $info = strtolower( $_SERVER['HTTP_USER_AGENT'] );
      
          $this->crlf = ( strpos( $info, "windows" ) === false ) ? "\n" : "\r\n" ;
          
          unset( $this->bi_a );
      }

	 // Implementation of parse_ini_file
   // by Hugo Gonçalves (hugo_goncalves@portugalmail.pt)
	 
   function parse_ini_file($filename)
	 {
		// Alocate the result array
		$res = array();
		// Does the file exists and can we read it?
		if(file_exists($filename) && is_readable($filename))
		{
			// In the beggining we are not in a section
			$section = "";
			// Open the file
			$fd = @fopen($filename,"r");
			if (flock($fd, LOCK_EX + LOCK_NB)) { // realizar un bloqueo exclusivo
				// Read each line
				while(!feof($fd))
				{
					// Read the line and trim it
					$line = trim(@fgets($fd, 4096 ));
					
					$len = strlen($line);
					// Only process non-blank lines
					if($len != 0)
					{
						// Only process non-comment lines
						if($line[0] != ';')
						{
							// Found a section?
							if(($line[0] == '[') && ($line[$len-1] == ']'))
							{
								// Get section name
								$section = substr($line,1,$len-2);
								// Check if the section is already included in result array						
								if(!isset($res[$section]))
								{
									// If not included create it
									$res[$section] = array();
								}
							}
							// Check for entries
							$pos = strpos($line,'=');
							// Found an entry
							if($pos != false)
							{
								// get name of entry and [Joao Borges] delete any blank spaces (begin and end)
								$name = trim(substr($line,0,$pos));

								// get value of entry and [Joao Borges] delete blank spaces again
								$value = trim(substr($line,$pos+1,$len-$pos-1));

							      // [Joao Borges] BugFix: Treating the " and ' characters
							      $value = str_replace('"','\\"',$value);
							      $value = str_replace("\'",'\\\'',$value);
		     

								// Store entry
								// If we are inside a section
								if($section != "")
								{
									$res[$section][$name] = $value;
								}
								else						
								{
									$res[$name] = $value;
								}
							}
						}
					}				
				}
			}
			// Close the file
			@fclose($fd);
		}
		return $res;
	}

      function show_ini( $path )
      {
            $this->filePATH = $path ;
            $INIarray = $this->parse_ini_file( $path );
            
            $fileCONTENTS = "" ;
            
            $c1 = 0 ;
            foreach ($INIarray as $i => $a)
            {
			$c2 = 0 ;
			foreach ($a as $n => $value)
			{ 
			    if ( $c2 == 0 ) $fileCONTENTS .= "[$i]<br/>$this->CRLF$n=$value<br/>$this->CRLF";
			    else if ( strlen( $value ) != 0 ) $fileCONTENTS .= "$n=$value<br/>$this->CRLF";
			    $c2++;
			}

			$fileCONTENTS .= "<br/>$this->CRLF" ;
            }
            
            $fileCONTENTS = substr( $fileCONTENTS, 0, strlen( $fileCONTENTS ) - ( 5 + strlen( "<br/>" ) ) );
            
            echo "<code>$fileCONTENTS</code>" ;
      }

      function save_ini( $path )
      {
            $fileCONTENTS = "" ;
            
            $c1 = 0 ;
            foreach ($this->bi_a as $i => $a)
            { 
                $c2 = 0 ;
                foreach ($a as $n => $value)
                { 
                    if ( $c2 == 0 ) $fileCONTENTS .= "[$i]$this->crlf$n=$value$this->crlf";
                    else if ( strlen( $value ) != 0 ) $fileCONTENTS .= "$n=$value$this->crlf";
                    $c2++;
                }
                
                $fileCONTENTS .= $this->crlf ;
            }
            
            $hFile = @fopen( $path, "w+" );
	    if (flock($hFile, LOCK_EX + LOCK_NB)) { // realizar un bloqueo exclusivo
		@fwrite( $hFile, $fileCONTENTS );
	    }
            @fclose( $hFile );
            
            unset( $this->bi_a );
      }

      //////////////////////////////////////////////////////////
      function find_entry( $path, $keyNAME, $entryNAME )
      {
            $this->filePATH = $path ;
            $INIarray = $this->parse_ini_file( $path );
            
            foreach ($INIarray as $i => $a)
            { 
                foreach ($a as $n => $value)
                { 
                    if ( strcmp( $i, $keyNAME ) == 0 && strcmp( $n, $entryNAME ) == 0 )
                    { 
                        return true ;
                    }
                }
            }

            return false ;
      }
      //////////////////////////////////////////////////////////
      function get_entry( $path, $keyNAME, $entryNAME )
      {
            $this->filePATH = $path ;
            $INIarray = $this->parse_ini_file( $path );
      
            foreach ($INIarray as $i => $a)
            { 
                foreach ($a as $n => $value)
                { 
                    if ( strcmp( $i, $keyNAME ) == 0 && strcmp( $n, $entryNAME ) == 0 )
                    { 
                        return $value ;
                    }
                }
            }

            return "" ;
      }
      //////////////////////////////////////////////////////////
      function add_entry( $path, $keyNAME, $entryNAME, $entryVALUE )
      {
            if ( $this->find_entry( $path, $keyNAME, $entryNAME ) )
            {
                $this->set_entry( $path, $keyNAME, $entryNAME, $entryVALUE ) ;
                return ;
            }
            
            $this->filePATH = $path ;
            $INIarray = $this->parse_ini_file( $path );
      
            $this->bi_a = array();
            
            $bKEYfound = false ;
            $bKEYadded = false ;
            
            foreach ($INIarray as $i => $a)
            { 
                foreach ($a as $n => $value)
                { 
                    if ( strcmp( $i, $keyNAME ) == 0 ) $bKEYfound = true ;
                    
                    $this->bi_a[$i][$n] = $a[$n] ;
                }
            
                if ( $bKEYfound )
                { 
                    $this->bi_a[$i][$entryNAME] = $entryVALUE ;
                    $bKEYfound = false ;
                    $bKEYadded = true ;
                }
            }

          if ( !$bKEYadded ) $this->bi_a[$keyNAME][$entryNAME] = $entryVALUE ;

          $this->save_ini( $path );
      }


      //////////////////////////////////////////////////////////
      function set_entry( $path, $keyNAME, $entryNAME, $entryVALUE )
      {
            $this->filePATH = $path ;
            $INIarray = $this->parse_ini_file( $path );
      
            $this->bi_a = array();

            foreach ($INIarray as $i => $a)
            { 
                foreach ($a as $n => $value)
                { 
                    if ( strcmp( $i, $keyNAME ) == 0 && strcmp( $n, $entryNAME ) == 0 )
                    { 
                        $this->bi_a[$i][$n] = $entryVALUE ;
                    }
                    else $this->bi_a[$i][$n] = $a[$n] ;
                }
            }

          $this->save_ini( $path );
      }

      //////////////////////////////////////////////////////////
      function delete_entry( $path, $keyNAME, $entryNAME )
      {
            $this->filePATH = $path ;
            $INIarray = $this->parse_ini_file( $path );
      
            $this->bi_a = array();

            foreach ($INIarray as $i => $a)
            { 
                foreach ($a as $n => $value)
                { 
                    if ( strcmp( $i, $keyNAME ) == 0 && strcmp( $n, $entryNAME ) == 0 )
                    { 
                        // don't do anything !
                    }
                    else $this->bi_a[$i][$n] = $a[$n] ;
                }
            }

          $this->save_ini( $path );
      }

      function delete_key( $path, $keyNAME )
      {
            $this->filePATH = $path ;
            $INIarray = $this->parse_ini_file( $path );
      
            $this->bi_a = array();

            foreach ($INIarray as $i => $a)
            { 
                foreach ($a as $n => $value)
                { 
                    if ( strcmp( $i, $keyNAME ) == 0 )
                    { 
                        // don't do anything !
                    }
                    else $this->bi_a[$i][$n] = $a[$n] ;
                }
            }

          $this->save_ini( $path );
      }
      
      var $bi_a ;
      var $filePATH ;
      var $crlf ;
}

?>
