<?php declare(strict_types=1);

namespace Przeslijmi\ConfigReady;

/**
 * Tool that scans all Apps in search of `config/specimen.php` file and copys them to library `.config/` dir.
 */
class Maker
{

    /**
     * All found specimens.
     *
     * ## One record example
     * ```
     * \\ [
     * \\     'vendor'    => 'przeslijmi',
     * \\     'app'       => 'sirouter',
     * \\     'uri'       => 'vendor/przeslijmi/sirouter/config/specimen.php',
     * \\     'finalName' => '.config.przeslijmi.sirouter.php',
     * \\ ]
     *
     * @var   array[]
     * @since v1.0
     */
    private $specimens = [];

    /**
     * Main working method - does the job.
     *
     * @return self
     * @since  v1.0
     */
    public function make() : self
    {

        // Create config dir.
        if (file_exists('config/') === false) {
            mkdir('config/');
        }

        // Scan dirs to look for `specimen.php` file in every app.
        $this->findSpecimens();

        // Now work on every specimen.
        foreach (array_keys($this->specimens) as $specimenKey) {
            $this->installSpecimen($specimenKey);
        }

        return $this;
    }

    /**
     * This is called after work - it deletes caller file to stop calling ConfigMaker with every call to software.
     *
     * @param string $callerUri Caller URI to be deleted.
     *
     * @return self
     * @since  v1.0
     */
    public function deleteCaller(string $callerUri) : self
    {

        if (file_exists($callerUri)) {
            unlink($callerUri);
        }

        return $this;
    }

    /**
     * Finds vendors, apps and specimens and put them to `$this->specimens`.
     *
     * @return self
     * @since  v1.0
     */
    private function findSpecimens() : self
    {

        // Get list of vendors.
        $vendors = $this->listDir('vendor/');

        // For each vendor.
        foreach ($vendors as $vendor) {

            // Get list of apps.
            $apps = $this->listDir($vendor);

            // For each app.
            foreach ($apps as $app) {
                $this->findSpecimenInApp($app);
            }
        }

        return $this;
    }

    /**
     * Install specimen file into main `config/` folder.
     *
     * @param integer $key Key of specimen frm `$this->specimens`.
     *
     * @return self
     * @since  v1.0
     */
    private function installSpecimen(int $key) : self
    {

        // Lvd.
        $spec = $this->specimens[$key];

        // Calc final name.
        $spec['finalName'] = '.config.' . $spec['vendor'] . '.' . $spec['app'] . '.php';

        // Save specimen.
        $this->specimens[$key] = $spec;

        // Copy file.
        if (file_exists('config/' . $spec['finalName']) === false) {
            var_dump('copying');
            var_dump(copy($spec['uri'], 'config/' . $spec['finalName']));
        }

        return $this;
    }

    /**
     * Check if there is specimen in app and put it into `$this->specimens`.
     *
     * @param string $appUri Uri of application.
     *
     * @return self
     * @since  v1.0
     */
    private function findSpecimenInApp(string $appUri) : self
    {

        // Lvd.
        $location = $appUri . 'config/specimen.php';
        $vendor   = explode('/', $appUri)[1];
        $app      = explode('/', $appUri)[2];

        // If there is no specimen - end here.
        if (file_exists($location) === false) {
            return $this;
        }

        // Add existsing file to specimens.
        $this->specimens[] = [
            'vendor' => $vendor,
            'app'    => $app,
            'uri'    => $location,
        ];

        return $this;
    }

    /**
     * List directories of given directory (returns elements names including directory uri).
     *
     * @param string $dirUri Directory uri to scan.
     *
     * @return string[]
     * @since  v1.0
     */
    private function listDir(string $dirUri) : array
    {

        // Lvd.
        $dirUri = rtrim(str_replace('\\', '/', $dirUri), '/') . '/';
        $result = [];

        // Open dir.
        $dirHandle = opendir($dirUri);

        // Read dir.
        while (false !== ($element = readdir($dirHandle))) {

            // Ignore those.
            if ($element === '.' || $element === '..') {
                continue;
            }
            if (is_dir($dirUri . $element) === false) {
                continue;
            }

            // Save those.
            $result[] = $dirUri . rtrim(str_replace('\\', '/', $element), '/') . '/';
        }

        return $result;
    }
}
