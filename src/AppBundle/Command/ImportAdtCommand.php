<?php
namespace AppBundle\Command;
use AppBundle\Entity\Adresse;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ImportAdtCommand extends ContainerAwareCommand
{
    const RESPONSE_SUCCESS = 0;

    const RESPONSE_EXCEPTION = 1;

    const RESPONSE_FILE_NOT_FOUND = 2;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $style;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $truncate;

    /**
     * @var string
     */
    protected $force;

    protected function configure()
    {
        $this
            ->setName('import:adt')
            ->setDescription('Import des informations administrative du patient et des données des medecins.')

            ->setHelp(
                <<<'EOT'
Import des informations administrative du patient et des données des medecins et met a jour/ajoute la base de donnees
EOT
            )
            ->addOption(
                'truncate',
                null,
                InputOption::VALUE_NONE,
                'vider les tables'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'executer les modifications sur la base de donnees'
            );
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input, $output);

        $this->truncate = $input->getOption('truncate') ? true : false;
        $this->force = $input->getOption('force') ? true : false;

        $this->style->title(sprintf(
            'Import Import des informations administrative du patient et des données des medecins.'
        ));

        $this->fileSystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->manager = $this->getContainer()->get('doctrine')->getManager();

            if (true === $this->truncate) {
                $this->style->caution('les tables seront videes');

                $this->truncateTables();
            }

            if (true === $this->force) {
                $this->style->caution('les modifications seront executees sur base de donnees');
            } else {
                $this->style->comment(
                    'les modifications seront ignorees, utilisez --force pour appliquer les modification'
                );
            }

            if (true === $this->force) {
                /** @var string $fixturesFilePath */
                $fileDir = $this->getContainer()->getParameter('file_path');
                $files = scandir($fileDir, 1);
                foreach ($files as $file) {
                    if (! in_array($file, array(".",".."))) {
                         $filePath = $fileDir . "/" . $file;
                        if (! $this->fileSystem->exists($filePath)) {
                            continue;
                        }

                        $this->importData($filePath);
                    }
                }
            }

            return self::RESPONSE_SUCCESS;
        } catch (FileNotFoundException $e) {
            $this->style->error($e->getMessage());

            return self::RESPONSE_FILE_NOT_FOUND;
        } catch (\Exception $e) {
            $this->style->error($e->getMessage());

            return self::RESPONSE_EXCEPTION;
        }
    }

    /**
     * @param $segement
     * @param Patient $patient
     *
     * @return Medecin|null
     */
    protected function importMedecinData($segement, $patient)
    {
        $datas = explode('|',$segement);
        if (! empty($datas[4])) {
            $medecinDatas = explode('^',$datas[4]);
            if (! empty($medecinDatas[12]) && $medecinDatas[12] === 'RPPS') {
                $nom = isset($medecinDatas[1]) ? $medecinDatas[1] : null;
                $prenom = isset($medecinDatas[2]) ? $medecinDatas[2] : null;
                $rpps = isset($medecinDatas[0]) ? $medecinDatas[0] : null;
                $medecin = $this->manager->getRepository('AppBundle:Medecin')->findOneByRpps($rpps);

                if (!$medecin instanceof Medecin) {
                    $medecin = new Medecin();
                    $medecin ->addPatiente($patient);
                }
                $medecin
                    ->setNom($nom)
                    ->setPrenom($prenom)
                    ->setRpps($rpps)
                ;

                $this->manager->persist($medecin);
                return $medecin;
            }
        }

        return null;
    }

    /**
     * @param string $segement
     *
     * @return Patient|null
     */
    protected function importPatientData($segement)
    {
        $patientDatas = explode('|',$segement);
        if (! empty($patientDatas[5])) {
            $nomPrenom = explode('^',$patientDatas[5]);

            $nom = isset($nomPrenom[0]) ? $nomPrenom[0] : null;
            $prenom = isset($nomPrenom[1]) ? $nomPrenom[1] : null;
            $dateNaissance = isset($patientDatas[7]) ? $patientDatas[7] : null;
            $genre = isset($patientDatas[8]) ? $patientDatas[8] : null;
            $identifiant = isset($patientDatas[3]) ? $patientDatas[3] : null;
            $identifiant = explode('^', $identifiant)[0];
            $patient = $this->manager->getRepository('AppBundle:Patient')->findOneByIdentifiant($identifiant);
            if (! $patient instanceof Patient) {
                $patient = new Patient();
            }

            $patient
                ->setNom($nom)
                ->setPrenom($prenom)
                ->setDateNaissance(new \DateTime($dateNaissance))
                ->setGenre($genre)
                ->setIdentifiant($identifiant)
            ;
            $this->manager->persist($patient);

            return [$patient, $patientDatas[11]];
        }

        return null;
    }

    /**
     * @param Patient $patient
     * @param string $sousSegment
     *
     * @return Adresse|null
     */
    protected function importAdresseData($sousSegment, $patient)
    {
        if (! empty($sousSegment)) {
            $sousSegment = explode('^', $sousSegment);
            $codePostal = isset($sousSegment[4]) ? $sousSegment[4] : null;
            $rue = isset($sousSegment[0]) ? $sousSegment[0] : null;
            $ville = isset($sousSegment[2]) ? $sousSegment[2] : null;

            $adresse = new Adresse();
            $adresse
                ->setCodePostal($codePostal)
                ->setRue($rue)
                ->setVille($ville)
            ;
            //on test si le patient exist dèjà avec la même adresse
            $existAdresse = false;
            /** @var Adresse $oldAdresse */
            foreach ($patient->getAdresses() as $oldAdresse) {
                if (
                    $adresse->getRue() == $oldAdresse->getRue() &&
                    $adresse->getVille() == $oldAdresse->getVille() &&
                    $adresse->getCodePostal() == $oldAdresse->getCodePostal()
                ) {
                    $existAdresse = true;
                    break;
                }
            }
            // s'il a un nouveau adresse je l'ajoute
            if (! $existAdresse) {
                $adresse->setPatient($patient);

                $this->manager->persist($adresse);
            }
            return $adresse;
        }

        return null;
    }

    /**
     * @param string $filePath
     */
    protected function importData($filePath) {
        //ouverture du fichier en lecture
        $handle = @fopen($filePath, "r");
        if ($handle) {
            $patient = null;
            $medecin = null;
            while (($buffer = fgets($handle, 4096)) !== false) {
                //Pour un patient
                if (preg_match('/PID/', $buffer) > 0) {
                    /** @var Patient $patient */
                    list($patient, $sousSegement) = $this->importPatientData($buffer);
                    if ($patient !== null) {
                        $this->style->success('Patient '.$patient->getIdentifiant());
                        /** @var Adresse $adresse */
                        $this->importAdresseData($sousSegement, $patient);
                    }

                }
                //Pour un medecin
                if (preg_match('/ROL/', $buffer) > 0) {
                    /** @var Medecin $medecin */
                    $medecin = $this->importMedecinData($buffer, $patient);
                    if ($medecin !== null) {
                        $this->style->success('Médecin '.$medecin->getRpps());
                    }
                }

                if ($medecin !== null && $patient !== null) {
                    $this->manager->flush();
                    $this->manager->clear();
                }
            }
            if (!feof($handle)) {
                $this->style->error('Erreur: fgets() a échoué');
            }
            fclose($handle);
        }
    }

    /**
     * Purge des tables patient, medecin et adresse.
     */
    protected function truncateTables()
    {
        $connection = $this->manager->getConnection();

        $connection
            ->executeUpdate(
                sprintf(
                    'TRUNCATE %s, %s, %s RESTART IDENTITY CASCADE;',
                    $this->manager->getClassMetadata('AppBundle:Medecin')->getTableName(),
                    $this->manager->getClassMetadata('AppBundle:Patient')->getTableName(),
                    $this->manager->getClassMetadata('AppBundle:Adresse')->getTableName()
                )
            );
    }
}