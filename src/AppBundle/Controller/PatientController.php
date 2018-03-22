<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PatientController extends Controller
{

    /**
     * @Route("/patients", name="patients")
     */
    public function patientsAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $patients = $manager->getRepository('AppBundle:Patient')->findAll();

        return $this->render('patient/patients.html.twig', ['patients' => $patients]);
    }


    /**
     * @Route("/patients/medecin/{rpps}", name="patients_du_medecin")
     * @param $rpps
     *
     * @return Response
     */
    public function patientsDuMedecinAction($rpps)
    {
        $manager = $this->getDoctrine()->getManager();
        $patients = $manager->getRepository('AppBundle:Patient')->findPatientParMedecin($rpps);


        $medecin = $manager->getRepository('AppBundle:Medecin')->findOneByRpps($rpps);

        return $this->render('patient/patients-du-medecin.html.twig', ['patients' => $patients, 'medecin' => $medecin]);
    }
}
