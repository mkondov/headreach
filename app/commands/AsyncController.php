<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;

use app\models\CommandsModel;
use app\models\HrQuery;
use app\models\Job;
use app\models\Keyword;
use app\models\Sumo;
use app\models\SumoArticle;
use app\models\LinkedinScraper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Task;
use app\models\Influencer;
use app\models\IDMasking;


/**
 * This controller would handle asynchronous tasks for HeadReach
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AsyncController extends Controller
{

    public $pid = array();
    public $lockDirPath = '/home/dogostz/cron-locks/';

    /**
     * This command would loop through the commands table and would execute all un-executed tasks
     */
    public function actionIndex($message = 'hello world') {
        // $this->executeTasks();
        if($this->lock('asynctasks')) {
            $this->executeTasks();
            $this->unlock('asynctasks');
        }
    }

    public function executeTasks() {
    	$commandsModel = new CommandsModel();

		$args = array (
			'executed' => 0,
		);

		while ( true ) {
			$commands = $commandsModel->findAll( $args );

			if ( empty($commands) ) {
				sleep( 2 );
				continue;
			}

			foreach ($commands as $command) {
				// exec('bash -c "exec nohup setsid '. $command->command .' > /dev/null 2>&1 &"');
				// exec( $command->command . ' 2>&1', $output );

				$task = str_replace('php ', '', $command->command);
				$task = '/usr/local/bin/php /home/dogostz/webapps/headreach/app/' . $task;

				exec( $task . ' > /dev/null &' );
				$command->executed = 1;
				$command->update();
			}
			
			sleep( 2 );
		}

    }

    public function lock($name='index',$die=true) {

        $lock_file = $this->lockDirPath . $name . '.lock';

        if (file_exists($lock_file) ) {
            // Is running?
            $this->pid[$name] = file_get_contents($lock_file);
            if ( $this->isrunning($name) ) {
                echo( '==' . $this->pid[$name] . '== Already in progress...');
                return false;
            } else {
                echo( '==' . $this->pid[$name] . '== Previous job died abruptly...');
            }
        }

        $this->pid[$name] = getmypid();
        file_put_contents( $lock_file, $this->pid[$name] );
        echo( '==' . $this->pid[$name] . '== Lock acquired, processing the job...' );

        return $this->pid[$name];
    }

    public function unlock($name='index') {

        $lock_file = $this->lockDirPath . $name . '.lock';

        if ( file_exists($lock_file) ) {
            unlink( $lock_file );
        }

        echo( '==' . $this->pid[$name] . '== Releasing lock...' );

        return true;
    }

    private function isrunning($name='index') {

        exec( "/bin/ps -e | awk '{print $1}'", $pids );
        if ( in_array($this->pid[$name], $pids) ) {
            return true;
        }

        return false;
    }

}