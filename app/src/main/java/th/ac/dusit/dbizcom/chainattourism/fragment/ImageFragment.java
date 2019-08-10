package th.ac.dusit.dbizcom.chainattourism.fragment;

import android.content.Context;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.widget.CircularProgressDrawable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import com.bumptech.glide.Glide;

import th.ac.dusit.dbizcom.chainattourism.R;
import th.ac.dusit.dbizcom.chainattourism.net.ApiClient;

public class ImageFragment extends Fragment {

    private static final String ARG_IMAGE = "image";

    private String mImageFileName;

    private ImageFragmentListener mListener;

    public ImageFragment() {
        // Required empty public constructor
    }

    public static ImageFragment newInstance(String imageFileName) {
        ImageFragment fragment = new ImageFragment();
        Bundle args = new Bundle();
        args.putString(ARG_IMAGE, imageFileName);
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            mImageFileName = getArguments().getString(ARG_IMAGE);
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_image, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(view.getContext());
        circularProgressDrawable.setStrokeWidth(5f);
        circularProgressDrawable.setCenterRadius(30f);
        circularProgressDrawable.start();

        ImageView imageView = view.findViewById(R.id.image_view);
        Glide.with(view.getContext())
                .load(ApiClient.GALLERY_BASE_URL.concat(mImageFileName))
                .placeholder(circularProgressDrawable)
                .into(imageView);
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
        if (context instanceof ImageFragmentListener) {
            mListener = (ImageFragmentListener) context;
        } else {
            throw new RuntimeException(context.toString()
                    + " must implement ImageFragmentListener");
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    public interface ImageFragmentListener {
    }
}
