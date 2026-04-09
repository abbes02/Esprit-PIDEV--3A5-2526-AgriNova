<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Entity\Utilisateur;
use App\Form\MaterielType;
use App\Repository\LocationRepository;
use App\Repository\MaterielRepository;
use App\Repository\PanneRepository;
use App\Service\MaterielStateManager;
use App\Service\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/materiel')]
final class MaterielController extends AbstractController
{
    public function __construct(
        private readonly UserContext $userContext,
        private readonly MaterielStateManager $materielStateManager,
    ) {
    }

    #[Route('/', name: 'app_materiel_index', methods: ['GET'])]
    public function index(MaterielRepository $materielRepository, LocationRepository $locationRepository, PanneRepository $panneRepository, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $currentUserId = (int) $currentUser->getId();
        $materiels = $materielRepository->findMarketplaceItems();

        if ($this->migrateLegacyLocalImagePaths($materiels)) {
            $entityManager->flush();
        }

        return $this->render('materiel/index.html.twig', [
            'materiels' => $materiels,
            'stats' => [
                'records' => count($materiels),
                'active_locations' => $locationRepository->countActiveByUtilisateurId($currentUserId),
                'unresolved_pannes' => $panneRepository->countUnresolvedByOwnerId($currentUserId),
            ],
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/new', name: 'app_materiel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();

        $materiel = new Materiel();
        $formBuilder = $this->createFormBuilder($materiel);
        MaterielType::build($formBuilder);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        $submittedImage = $this->getSubmittedImageFile($form);
        $uploadedImage = $this->prepareUploadedImage($form, $materiel, $submittedImage);
        $this->ensureImagePresent($form, $materiel, $submittedImage);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($uploadedImage instanceof UploadedFile) {
                try {
                    $this->storeUploadedImage($uploadedImage, (string) $materiel->getImageUrl());
                } catch (FileException) {
                    $form->addError(new FormError('Unable to upload image. Please try again.'));
                }
            }

            if (!$form->isValid()) {
                return $this->render('materiel/new.html.twig', [
                    'materiel' => $materiel,
                    'form' => $form,
                    'current_user' => $currentUser,
                ]);
            }

            $materiel->setProprietaire($currentUser);
            $materiel->setEtat(Materiel::ETAT_DISPONIBLE);
            $materiel->setDateAjout($materiel->getDateAjout() ?? new \DateTime());

            $entityManager->persist($materiel);
            $entityManager->flush();

            $this->materielStateManager->refreshForMateriel($materiel);
            $this->addFlash('success', 'Materiel cree avec succes.');

            return $this->redirectToRoute('app_materiel_index', ['user_id' => $currentUser->getId()]);
        }

        return $this->render('materiel/new.html.twig', [
            'materiel' => $materiel,
            'form' => $form,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/{id}', name: 'app_materiel_show', methods: ['GET'])]
    public function show(Materiel $materiel): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertOwnedByCurrentUser($materiel, $currentUser);

        return $this->render('materiel/show.html.twig', [
            'materiel' => $materiel,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_materiel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Materiel $materiel, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertOwnedByCurrentUser($materiel, $currentUser);

        $formBuilder = $this->createFormBuilder($materiel);
        MaterielType::build($formBuilder);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        $submittedImage = $this->getSubmittedImageFile($form);
        $uploadedImage = $this->prepareUploadedImage($form, $materiel, $submittedImage);
        $this->ensureImagePresent($form, $materiel, $submittedImage);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($uploadedImage instanceof UploadedFile) {
                try {
                    $this->storeUploadedImage($uploadedImage, (string) $materiel->getImageUrl());
                } catch (FileException) {
                    $form->addError(new FormError('Unable to upload image. Please try again.'));
                }
            }

            if (!$form->isValid()) {
                return $this->render('materiel/edit.html.twig', [
                    'materiel' => $materiel,
                    'form' => $form,
                    'current_user' => $currentUser,
                ]);
            }

            $entityManager->flush();
            $this->materielStateManager->refreshForMateriel($materiel);
            $this->addFlash('success', 'Materiel mis a jour avec succes.');

            return $this->redirectToRoute('app_materiel_index', ['user_id' => $currentUser->getId()]);
        }

        return $this->render('materiel/edit.html.twig', [
            'materiel' => $materiel,
            'form' => $form,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/{id}', name: 'app_materiel_delete', methods: ['POST'])]
    public function delete(Request $request, Materiel $materiel, MaterielRepository $materielRepository, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertOwnedByCurrentUser($materiel, $currentUser);

        if ($this->isCsrfTokenValid('delete_materiel_' . $materiel->getId(), (string) $request->request->get('_token'))) {
            if ($materielRepository->hasLinkedRows((int) $materiel->getId())) {
                $this->addFlash('error', 'Suppression refusee: ce materiel est lie a des locations ou pannes.');
            } else {
                $entityManager->remove($materiel);
                $entityManager->flush();
                $this->addFlash('success', 'Materiel supprime avec succes.');
            }
        }

        return $this->redirectToRoute('app_materiel_index', ['user_id' => $currentUser->getId()]);
    }

    private function assertOwnedByCurrentUser(Materiel $materiel, Utilisateur $currentUser): void
    {
        if ($materiel->getProprietaire()?->getId() !== $currentUser->getId()) {
            throw $this->createNotFoundException();
        }
    }

    private function getSubmittedImageFile($form): ?UploadedFile
    {
        if (!$form->isSubmitted() || !$form->has('imageFile')) {
            return null;
        }

        $uploadedImage = $form->get('imageFile')->getData();

        if (!$uploadedImage instanceof UploadedFile) {
            return null;
        }

        return $uploadedImage;
    }

    private function prepareUploadedImage($form, Materiel $materiel, ?UploadedFile $uploadedImage): ?UploadedFile
    {
        if (!$uploadedImage instanceof UploadedFile) {
            return null;
        }

        if (!$uploadedImage->isValid()) {
            $form->get('imageFile')->addError(new FormError($this->resolveUploadErrorMessage($uploadedImage)));

            return null;
        }

        $mimeType = (string) $uploadedImage->getMimeType();
        $extension = strtolower((string) $uploadedImage->guessExtension());

        if ($extension === '') {
            $extension = strtolower((string) pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_EXTENSION));
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $isAllowedMime = $mimeType !== '' && str_starts_with($mimeType, 'image/');
        $isAllowedExtension = in_array($extension, $allowedExtensions, true);

        if (!$isAllowedMime && !$isAllowedExtension) {
            $form->get('imageFile')->addError(new FormError('Unsupported image format. Use JPG, PNG, GIF, WEBP, or BMP.'));

            return null;
        }

        $materiel->setImageUrl($this->buildUploadRelativePath($uploadedImage));

        return $uploadedImage;
    }

    private function ensureImagePresent($form, Materiel $materiel, ?UploadedFile $submittedImage): void
    {
        if (!$form->isSubmitted()) {
            return;
        }

        if ($submittedImage instanceof UploadedFile) {
            return;
        }

        $currentImage = trim((string) $materiel->getImageUrl());

        if ($currentImage === '' && $form->has('imageFile')) {
            $form->get('imageFile')->addError(new FormError('Please upload an image file.'));
        }
    }

    private function buildUploadRelativePath(UploadedFile $uploadedImage): string
    {
        $extension = (string) $uploadedImage->guessExtension();

        if ($extension === '') {
            $extension = 'jpg';
        }

        return sprintf(
            'uploads/materiel/%s-%s.%s',
            date('YmdHis'),
            bin2hex(random_bytes(5)),
            $extension
        );
    }

    private function storeUploadedImage(UploadedFile $uploadedImage, string $relativePath): void
    {
        [$targetDirectory, $fileName] = $this->resolveUploadDestination($relativePath);
        $uploadedImage->move($targetDirectory, $fileName);
    }

    private function resolveUploadErrorMessage(UploadedFile $uploadedImage): string
    {
        return match ($uploadedImage->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Image is too large for upload. Try a smaller file.',
            UPLOAD_ERR_PARTIAL => 'Upload was interrupted. Please retry.',
            UPLOAD_ERR_NO_FILE => 'No image file was selected.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'Server could not process the uploaded file. Please retry.',
            default => 'Upload failed. Please pick the file again.',
        };
    }

    /**
     * @param Materiel[] $materiels
     */
    private function migrateLegacyLocalImagePaths(array $materiels): bool
    {
        $hasChanges = false;

        foreach ($materiels as $materiel) {
            $currentImage = trim((string) $materiel->getImageUrl());
            $localPath = $this->extractLocalFilePath($currentImage);

            if ($localPath === null || !is_file($localPath)) {
                continue;
            }

            try {
                $materiel->setImageUrl($this->copyLocalImageToUploads($localPath));
                $hasChanges = true;
            } catch (FileException) {
                continue;
            }
        }

        return $hasChanges;
    }

    private function extractLocalFilePath(string $rawPath): ?string
    {
        $path = trim($rawPath);

        if ($path === '') {
            return null;
        }

        if (str_starts_with(strtolower($path), 'file:/')) {
            $path = rawurldecode((string) preg_replace('#^file:/+#i', '', $path));

            if (preg_match('#^/[A-Za-z]:/#', $path) === 1) {
                $path = substr($path, 1);
            }

            return str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1) {
            return str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        return null;
    }

    private function copyLocalImageToUploads(string $localPath): string
    {
        $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));

        if ($extension === '') {
            $extension = 'jpg';
        }

        $relativePath = sprintf(
            'uploads/materiel/%s-%s.%s',
            date('YmdHis'),
            bin2hex(random_bytes(5)),
            $extension
        );

        [$targetDirectory, $fileName, $absolutePath] = $this->resolveUploadDestination($relativePath, true);

        if (!copy($localPath, $absolutePath)) {
            throw new FileException('Failed to copy local image file');
        }

        if (!is_file($absolutePath)) {
            throw new FileException('Copied file is missing after copy operation');
        }

        return $relativePath;
    }

    /**
     * @return array{0:string,1:string}|array{0:string,1:string,2:string}
     */
    private function resolveUploadDestination(string $relativePath, bool $includeAbsolute = false): array
    {
        if ($relativePath === '') {
            throw new FileException('Missing destination path');
        }

        $absolutePath = dirname(__DIR__, 2) . '/public/' . ltrim($relativePath, '/');
        $targetDirectory = dirname($absolutePath);
        $fileName = basename($absolutePath);

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
            throw new FileException('Cannot create upload directory');
        }

        if ($includeAbsolute) {
            return [$targetDirectory, $fileName, $absolutePath];
        }

        return [$targetDirectory, $fileName];
    }
}
